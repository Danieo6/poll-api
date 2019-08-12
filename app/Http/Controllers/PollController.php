<?php

namespace App\Http\Controllers;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Poll;
use App\PollAnswer;
use App\Vote;

class PollController extends Controller
{
    //* Disable Auth middleware for poll creation and voting
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create', 'vote', 'get']]);
    }

    /**
     * * Get poll by ID
     * @param id Poll ID
     */
    public function get(Request $req, $id)
    {
        if (!is_numeric($id))
            return response()->json(['error' => 'id_nan'], 400);

        $poll = Poll::with(['answers' => function($query){
            $query->withcount('votes');
        }])->find($id);

        return response()->json(['poll' => $poll], 200);
    }

    //* Create a new poll
    public function create(Request $req)
    {
        // If the user isn't authorized, set the ownership to GUEST
        $author = Auth::id();

        if ($author === null)
            $author = 0;

        // Basic validation
        $validator = Validator::make($req->all(), [
            'question' => 'required|string',
            'answers' => 'required',
            'close_time' => 'date',
            'multiple_answers' => 'required|boolean'
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        }

        // Get the close time, if it's not set use default value
        $closeTime = $req->close_time;

        if ($closeTime === NULL)
            $closeTime = date('Y-m-d', time()+2592000); //Default: close poll after 30 days

        // Create and save the poll
        $poll = new Poll;
        $poll->question = $req->question;
        $poll->author = $author;
        $poll->close_time = $closeTime;
        $poll->multiple_answers = $req->multiple_answers;
        $poll->save();

        // Create, connect to the poll and save answers
        $answers = [];
        foreach ($req->answers as $answer)
        {
            if (empty($answer))
            {
                continue;
            }

            array_push($answers, [
                'answer' => $answer,
                'poll' => $poll->id
            ]);
        }

        PollAnswer::insert($answers);

        // Everything seems okey!
        return response()->json(['status' => 'ok', 'pollId' => $poll->id], 200);
    }

    //* Vote to an poll
    public function vote(Request $req)
    {
        // Basic validation
        $validator = Validator::make($req->all(), [
        'poll' => 'required|integer|exists:polls,id',
        'answers' => 'required|array',
        'fingerprint' => 'required|alpha_num'
        ]);
        
        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        }

        // Check if someone isn't trying to send multiple answers when it's forbidden
        $poll = Poll::find($req->poll);

        if (!$poll->multiple_answers)
        {
            if (count($req->answers) > 1)
            {
                return response()->json(['errors' => ['Multiple answers forbidden!']], 400);
            }
        }

        // Check if the poll is closed
        if (time() >= strtotime($poll->close_time))
        {
            return response()->json(['errors' => ['Sorry! The poll has ended!']], 400);
        }

        // Get answers for this poll
        $pollAnswers = $this->array_flatten(PollAnswer::where('poll', $req->poll)->get()->makeHidden('answer')->toArray());

        // Check if user hasn't voted earlier
        $votesWithFingerprint = Vote::where('fingerprint', $req->fingerprint)->whereIn('answer', $pollAnswers)->get()->toArray();

        if (!empty($votesWithFingerprint))
        {
            return response()->json(['errors' => ['Wait! You have already voted!']], 400);
        }

        // Check if provided answer exist and is connected to this poll
        $votes = [];
        foreach ($req->answers as $answer)
        {
            if (!is_numeric($answer))
            {
                return response()->json(['errors' => ['Invalid answer ID']], 400);
            }

            if (!in_array($answer, $pollAnswers))
            {
                return response()->json(['errors' => ['Answer doesnt exist']], 400);
            }

            // If everything is fine, push the answer further
            array_push($votes, [
                'answer' => $answer,
                'fingerprint' => $req->fingerprint,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }

        // Save the votes
        Vote::insert($votes);

        // Everything seems okey!
        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * * Just a utility method for flatening multi-dimensional arrays
     * @param input The array to flatten
     */
    private function array_flatten($input)
    {
        $input = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($input));
        $output = [];

        foreach ($input as $value)
        {
          array_push($output, $value);
        }

        return $output;
    }
}
