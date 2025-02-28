<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;

require_once(config('app.phpPATH') . 'function.php');

class EnqueteController extends Controller
{
    //トップページ
    public function top()
    {

        // $command ='python ' . 'C:\Users\music\Documents\xampp\zettaionkan\app\Python\send_mail.py 2>&1';
        // exec($command, $output);
        // // $fp = fopen("output.txt", "w");
        // // fputcsv($fp, $output);
        // // fclose($fp); 
        // file_put_contents("sample.txt", $output, FILE_APPEND);
        return view('piano.top');
    }

    public function labolatory()
    {
        return view('piano.labolatory');
    }

    public function link()
    {
        return view('piano.link');
    }

    public function privacypolicy()
    {
        return view('piano.privacypolicy');
    }

    public function tame()
    {
        return view('piano.tame');
    }

    public function test_mail_send1()
    {
        return view('piano.test_mail_send1');
    }

    public function test_mail_send2()
    {
        return view('piano.test_mail_send2');
    }

    //閉鎖中ページ
    public function close()
    {
        return view('piano.close');
    }

    //アンケートページ
    public function start(Request $request)
    {
        //被験者が前半部分まで進めていたら中間ページへリダイレクト
        if ($request->session()->has('halfFlag')) {
            return redirect('half');
        } else {
            return view('piano.start');
        }
    }



    //アンケート回答確認ページ
    public function confirm(Request $request)
    {
        $name = $request->input('name');
        $sex = $request->input('sex');
        $age = $request->input('age');
        $learnPiano = $request->input('learnPiano');
        $musicJob = $request->input('musicJob');
        $absolutePitch = $request->input('absolutePitch');
        $ability = $request->input('ability');
        $symptom = $request->input('symptom');
        $result = $request->input('result');
        $mail = $request->input('mail');
        $request->session()->put('mail', $mail);
        $pdf = $request->input('pdf');

        if (isset($name)) {
            $isName = !is_null($request->input('name'));
            if ($isName) {
                $name = $request->input('name');
                $name = strip_tags($name);
                $name = es($name);
            }
        } else {
            $name = '';
            $isName = false;
        }

        if (isset($sex)) {
            $sexValues = ['男性', '女性'];
            $isSex = in_array($sex, $sexValues);
        } else {
            $isSex = false;
            $sex = '男性';
        }

        if (isset($age)) {
            mb_convert_kana($request->input('age'), $mode = 'a');
            $isAge = is_numeric($age);
        } else {
            $isAge = false;
            $age = '';
        }

        if (isset($learnPiano)) {
            $learnPiano = strip_tags($learnPiano);
            $learnPiano = es($learnPiano);
        } else {
            $learnPiano = '';
        }

        if (isset($musicJob)) {
            $YesNo = ['はい', 'いいえ'];
            $is_musicJob = in_array($musicJob, $YesNo);
        } else {
            $is_musicJob = false;
            $musicJob = 'いいえ';
        }

        if (isset($absolutePitch)) {
            $is_absolutePitch = in_array($absolutePitch, $YesNo);
        } else {
            $is_absolutePitch = false;
            $absolutePitch = 'いいえ';
        }

        if (isset($ability)) {
            $YesNoHave = ['はい', 'いいえ', '絶対音感を持っていない'];
            $is_ability = in_array($ability, $YesNoHave);
        } else {
            $is_ability = false;
            $ability = 'いいえ';
        }

        if (isset($symptom)) {
            $symptom = strip_tags($symptom);
            $symptom = es($symptom);
        } else {
            $symptom = '';
        }

        if (isset($result)) {
            $is_result = in_array($result, $YesNo);
        } else {
            $is_result = false;
            $result = 'いいえ';
        }

        if (isset($mail)) {
            $mail = strip_tags($mail);
            $mail = es($mail);
        } else {
            $mail = '';
        }

        if (!isset($pdf)) {
            $pdf = '希望しない';
        }

        $isSubmited = $isName && $isSex && $isAge && $is_ability && $is_absolutePitch && $is_musicJob && $is_result;
        if ($isSubmited) {
            $prof['name'] = $name;
            $prof['sex'] = $sex;
            $prof['age'] = $age;
            $prof['learnPiano'] = $learnPiano;
            $prof['musicJob'] = $musicJob;
            $prof['absolutePitch'] = $absolutePitch;
            $prof['ability'] = $ability;
            $prof['symptom'] = $symptom;
            $prof['result'] = $result;
            $prof['mail'] = $mail;
            $prof['pdf'] = $pdf;

            $request->session()->put('prof', $prof);

            $ismobile = is_mobile();
        } else {
            return view('piano.error');
        }

        return view('piano.confirm', [
            'name' => $name,
            'sex' => $sex,
            'age' => $age,
            'learnPiano' => $learnPiano,
            'musicJob' => $musicJob,
            'absolutePitch' => $absolutePitch,
            'ability' => $ability,
            'symptom' => $symptom,
            'ismobile' => $ismobile,
            'result' => $result,
            'mail' => $mail,
            'pdf' => $pdf
        ]);
    }

    //鍵盤や音源の確認ページ
    //ここでアンケート回答データをデータベースに格納
    public function instruct(Request $request)
    {
        $piano_rand = rand(0, 35);
        $guitar_rand = rand(36, 71);
        $pure_rand = rand(72, 107);

        date_default_timezone_set('Asia/Tokyo');
        $datetime = new DateTime();


        $sound = [
            'piano' => sprintf('%03d', $piano_rand),
            'guitar' => sprintf('%03d', $guitar_rand),
            'pure' => sprintf('%03d', $pure_rand),
        ];

        if ($request->session()->has('enqueteFlag')) {
            if (is_mobile()) {
                return view('piano.sp_instruct', $sound);
            } else {
                return view('piano.instruct', $sound);
            }
        } else {
            //セッションからprofを受け取った後削除
            $prof = $request->session()->pull('prof');

            $userdata = [
                'name' => $prof['name'],
                'sex' => $prof['sex'],
                'age' => $prof['age'],
                'learnPiano' => $prof['learnPiano'],
                'musicJob' => $prof['musicJob'],
                'absolutePitch' => $prof['absolutePitch'],
                'ability' => $prof['ability'],
                'symptom' => $prof['symptom'],
                'result' => $prof['result'],
                'mail' => $prof['mail'],
                'pdf' => $prof['pdf'],
                'datetime' => $datetime,
            ];

            //データベースに接続し、データ登録
            DB::insert('insert into enquete(namae,seibetsu,nenrei,q1,q2,q3,q4,q5,result,mail,pdf,date) values (:name,:sex,:age,:learnPiano,:musicJob,:absolutePitch,:ability,:symptom,:result,:mail,:pdf,:datetime)', $userdata);

            $request->session()->put('result', $userdata['result']);
            $request->session()->put('username', $userdata['name']);
            $request->session()->put('enqueteFlag', 'true');

            if (is_mobile()) {
                return view('piano.sp_instruct', $sound);
            } else {
                return view('piano.instruct', $sound);
            }
        }
    }

    //回答練習ページ
    public function prac01()
    {
        $test_sound = rand(0, 107);
        $sound = [
            'test_sound' => sprintf('%03d', $test_sound),
        ];
        if (is_mobile()) {
            return view('piano.sp_prac01', $sound);
        } else {
            return view('piano.prac01', $sound);
        }
    }

    public function prac02()
    {
        $test_sound = rand(0, 107);
        $sound = [
            'test_sound' => sprintf('%03d', $test_sound),
        ];
        if (is_mobile()) {
            return view('piano.sp_prac02', $sound);
        } else {
            return view('piano.prac02', $sound);
        }
    }

    public function prac03()
    {
        $test_sound = rand(0, 107);
        $sound = [
            'test_sound' => sprintf('%03d', $test_sound),
        ];
        if (is_mobile()) {
            return view('piano.sp_prac03', $sound);
        } else {
            return view('piano.prac03', $sound);
        }
    }

    //実験開始前のページ
    public function standby()
    {
        return view('piano.standby');
    }
}
