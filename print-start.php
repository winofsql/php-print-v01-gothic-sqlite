<?php
require_once("setting.php");
require_once("db_connect.php");
require_once("print-setting.php");

// ******************************
// 初期処理や変数の設定
// ******************************
// フォント選択とフォントのサイズ指定
$pdf->SetFont('ume-tgo4', '', 14);

$counter = 0;           // ページ用カウンタ
$rmax = 15;             // ページ内の最大明細行数
$lcount = 0;            // 次に印字する行位置

$row_height = 8;        // 行の高さ
$header_height = 40;    // ヘッダ部分の物理的な高さ

$init = true;           // 初回フラグ

// 現在の物理位置
$cur_position = $header_height;

// データの印字
$_POST["query"] = <<<QUERY
select 社員コード,氏名,フリガナ from 社員マスタ order by 社員コード
QUERY;

// クエリーの実行
$result = $mysqli->query( $_POST["query"] );
while ( $row = $result->fetch_array( MYSQLI_BOTH ) ) {

    // 初回のみヘッダを印字する
    if(  $init  ) {
        $init = false;
        // ページを追加
        $pdf->AddPage();
        // ヘッダーの出力
        print_header( $pdf );
    }

    // 改ページ コントロール
    $lcount += 1;
    // 仕様の最大行の主力を超えたら、次のページを作成する
    if ( $lcount > $rmax ) {
        // ページ追加
        $pdf->AddPage();

        // ページカウンタをカウントアップ
        $counter += 1;
        // ヘッダーの出力
        print_header( $pdf );

        // 行カウントを初期化する( 次に印字する行位置 )
        $lcount = 1;
        // 印字位置を先頭に持っていく
        $cur_position = $header_height;
    }

    user_text( $pdf, 10, $cur_position, $row["社員コード"] );
    user_text( $pdf, 28, $cur_position, $row["氏名"] );
    user_text( $pdf, 51+15, $cur_position, $row["フリガナ"] );

    $cur_position += $GLOBALS['row_height'];

}

$mysqli->close();

// ブラウザへ PDF を出力します
$pdf->Output("test_output.pdf", "I");

// ************************************
// ヘッダの印字
// ************************************
function print_header( $pdf ) {

    global $counter;

    $page_info = $pdf->getPageDimensions();

    // ヘッダ内での印字位置コントロール
    $cur_position = $page_info['tm'];	// トップマージン
    
    // ページの先頭
    $pdf->SetFont('ume-tgo4', '', 30);
    user_text( $pdf, 100,   $cur_position-4, "社員一覧表" );
    $pdf->SetFont('ume-tgo4', '', 14);

    user_text( $pdf, 224,   $cur_position, "ページ :" );
    user_text( $pdf, 250,   $cur_position, number_format($counter+1), 5, 0, "R" );
    
    // データのタイトル
    $cur_position += $GLOBALS['row_height'] * 2;    // 2行進む( 1行空ける )
    user_text( $pdf, 10,    $cur_position, "コード" );
    user_text( $pdf, 28,    $cur_position, "氏名" );
    user_text( $pdf, 51+15, $cur_position, "フリガナ" );
    
}

?>
