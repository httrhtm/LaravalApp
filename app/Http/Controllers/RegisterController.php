<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Question;
use App\CorrectAnswer;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 登録画面へ移動
     */
    public function create()
    {
        return view('register.create');
    }

    /**
     * 確認画面へ移動
     */
    public function confirm(Request $request)
    {
        //-----------------------------------
        // バリデーション
        //-----------------------------------
        $validated = $request->validate([
            'question' => ['required', 'string',' max:500'],
            'answer.*' => ['required.*', 'string', 'max:200'],
        ]);

        //-----------------------------------
        // パラメータを変数に代入
        //-----------------------------------
        $question = $request->$validated->question;
        $answers = $request->$validated->answers;

        //-----------------------------------
        // 確認画面へ移動
        //-----------------------------------
        return view('register.confirm', [
            'question' => $question,
            'answers' => $answers,
        ]);
    }

    /**
     * 登録処理
     *
     * 一覧画面へ移動
     */
    public function store(Request $request)
    {
        $question = new Question();
        $question->question = $request->question;
        $question->save();

        $answer = new CorrectAnswer();

        //-----------------------------------
        // 答えの入力値（配列）を変数に代入
        //-----------------------------------
        $inputs = $request->answers;

        //-----------------------------------
        // questions_idを取得
        //-----------------------------------
        $questions_id = DB::table('questions')->select('id')
        ->orderByRaw('created_at desc')
        ->limit(1)
        ->get();

        //-----------------------------------
        //JSON 文字列をデコードする
        //-----------------------------------
        $questions_id = json_decode($questions_id, true);

        //-----------------------------------
        //現在時刻を取得
        //-----------------------------------
        $now = Carbon::now();

        //-----------------------------------
        //配列の場合
        //-----------------------------------
        if (is_array($inputs)) {
            $array = [];
            //1つずつanswerに入れる
            for ($i = 0; $i < count($inputs); $i++) {

                $data = [
                    'answer' => $inputs[$i],
                    'questions_id' => $questions_id[0]['id'],
                    'created_at' => $now
                ];
                $array[]= $data; //データを配列に入れる
            }

            DB::table('correct_answers')-> insert($array);

        //-----------------------------------
        //配列以外の場合
        //-----------------------------------
        } else {
            $answer->answer = $inputs;
            $answer->questions_id = $questions_id[0]['id'];
            $answer->save();
        }


        return redirect('list');
    }
}
?>