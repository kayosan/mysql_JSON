<?php
/*****************************************************************************************
 　ぐるなびWebサービスのレストラン検索APIで緯度経度検索を実行しパースするプログラム
 　注意：緯度、経度、範囲の値は固定で入れています。
 　　　　アクセスキーはユーザ登録時に発行されたキーを指定してください。
*****************************************************************************************/
 
//エンドポイントのURIとフォーマットパラメータを変数に入れる
$uri   = "https://api.gnavi.co.jp/RestSearchAPI/20150630/";
//APIアクセスキーを変数に入れる
$acckey= "";
//返却値のフォーマットを変数に入れる
$format= "json";
//緯度・経度、範囲を変数に入れる
//緯度経度は日本測地系で日比谷シャンテのもの。範囲はrange=1で300m以内を指定している。
$lat   = 35.670083;
$lon   = 139.763267;
$range = 1;
 
//URL組み立て
$url  = sprintf("%s%s%s%s%s%s%s%s%s%s%s", $uri, "?format=", $format, "&keyid=", $acckey, "&latitude=", $lat,"&longitude=",$lon,"&range=",$range);
//API実行
$json = file_get_contents($url);
//取得した結果を\\オブジェクト化
$obj  = json_decode($json);
$link = mysql_connect('localhost','root',"password");
if (!$link) {
    die('接続失敗です。'.mysql_error());
}

$db_selected = mysql_select_db('firstmysql', $link);
if (!$db_selected){
    die('データベース選択失敗です。'.mysql_error());
}
mysql_set_charset('utf8');

 
//結果をパース
//トータルヒット件数、店舗番号、店舗名、最寄の路線、最寄の駅、最寄駅から店までの時間、店舗の小業態を出力
foreach((array)$obj as $key => $val){
   if(strcmp($key, "total_hit_count" ) == 0 ){
       echo "total:".$val."\n";
   }
 $count = 0;
   if(strcmp($key, "rest") == 0){
       foreach((array)$val as $restArray){
            if(checkString($restArray->{'id'}))   $count = $count + 1;
            if(checkString($restArray->{'name'})) $name = $restArray->{'name'}."\t";
            if(checkString($restArray->{'access'}->{'line'}))    $line = (string)$restArray->{'access'}->{'line'}."\t";
            if(checkString($restArray->{'access'}->{'station'})) $station = (string)$restArray->{'access'}->{'station'}."\t";
            if(checkString($restArray->{'access'}->{'walk'}))    $walk = (string)$restArray->{'access'}->{'walk'}."分\t";
 
            foreach((array)$restArray->{'code'}->{'category_name_s'} as $v){
                if(checkString($v)) echo $v."\t";
            }
            echo "\n";
           $sql = "INSERT INTO  gTest(id,name,line,station,walk) VALUES ($count,'$name','$line','$station','$walk')";
           $result_flag = mysql_query($sql);

           if (!$result_flag) {
            die('INSERTクエリーが失敗しました。'.mysql_error());
}

       }
 
   }
}
 
//文字列であるかをチェック
function checkString($input)
{
 
    if(isset($input) && is_string($input)) {
        return true;
    }else{
        return false;
    }
 
}
?>