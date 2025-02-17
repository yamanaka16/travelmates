<?php
session_start();
if (!isset($_SESSION['travel_id'])) {
    die('Error: travel_idが設定されていません。');
}
$travel_id = $_SESSION['travel_id'];
date_default_timezone_set('Asia/Tokyo'); // 必要に応じてタイムゾーンを設定

// POSTされたデータがあるか確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // plapost.phpから最後のルート番号を取得
    $route_number = $_POST['last_route_number'];
    //テスト用
    $plan_datil = $_POST['plan_detil'];
    // 現在の日付を取得（必要に応じて変更）
    $current_date = date('Y-m-d');
    $button_number = $_POST['routeNumber'];

    // タブ1（移動）のデータが送信されたかどうか確認
    if (isset($_POST['budget']) && isset($_POST['start']) && isset($_POST['end'])) {
        $charge = $_POST['budget'];
        $start = $_POST['start'];
        $start_hour = $_POST['start_hour'];
        $start_minute = $_POST['start_minute'];
        $end = $_POST['end'];
        $end_hour = $_POST['end_hour'];
        $end_minute = $_POST['end_minute'];
        // トランスポートIDに変更しておく
        $vehicle = $_POST['vehicle'];

        // 出発時刻と到着時刻をdatetime型に変換
        $start_datetime = $current_date . ' ' . sprintf('%02d', $start_hour) . ':' . sprintf('%02d', $start_minute) . ':00';
        $end_datetime = $current_date . ' ' . sprintf('%02d', $end_hour) . ':' . sprintf('%02d', $end_minute) . ':00';

        // データベース接続情報
        $dsn = 'mysql:host=localhost;dbname=travelmates;charset=utf8';
        $username = 'root';
        $password = 'root';
        $pdo = new PDO($dsn, $username, $password);

        try {
            $is_transport = 1;

            // エラーモードを例外モードに設定
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // SQLクエリを準備
            $stmt = $pdo->prepare('INSERT INTO event (route_id, travel_id, is_transport, charge, start_datetime, end_datetime, transport_id, place, event_detail) VALUES (:route_id, :travel_id, :is_transport, :charge, :start_datetime, :end_datetime, :transport_id, :place, :event_detail)');

            // 値をバインドしてSQLを実行
            $stmt->bindValue(':route_id', $button_number, PDO::PARAM_INT);
            $stmt->bindValue(':travel_id', $travel_id, PDO::PARAM_INT);  // 外部キーとして旅行ID
            $stmt->bindValue(':is_transport', $is_transport, PDO::PARAM_BOOL); 
            $stmt->bindValue(':charge', $charge, PDO::PARAM_INT);
            $stmt->bindValue(':start_datetime', $start_datetime, PDO::PARAM_STR);
            $stmt->bindValue(':end_datetime', $end_datetime, PDO::PARAM_STR);
            $stmt->bindValue(':transport_id', $vehicle, PDO::PARAM_INT);
            $stmt->bindValue(':place', $start, PDO::PARAM_STR);  // データ型を修正
            $stmt->bindValue(':event_detail', $end, PDO::PARAM_STR);  // データ型を修正

            // SQLを実行してデータを挿入
            $stmt->execute();

            // 正常に追加されたら次のページにリダイレクト
            header("Location: addplan.php");
            exit();
        } catch (PDOException $e) {
            // エラーが発生した場合、エラーメッセージを表示
            echo 'データベースエラー: ' . $e->getMessage();
        }
    } else{
        // タブ2（予定）のデータが送信されたかどうか確認
        $is_transport = 0;
        $place = $_POST['place'];
        $plan_detil = $_POST['plan_detil'];
        $plan_hour = $_POST['plan_hour'];
        $plan_minute = $_POST['plan_minute'];
        $button_number = $_POST['routeNumber'];
        // データベース接続情報
        $dsn = 'mysql:host=localhost;dbname=travelmates;charset=utf8';
        $username = 'root';
        $password = 'root';
        $pdo = new PDO($dsn, $username, $password);

        // 予定時刻をdatetime型に変換
        $plan_datetime = $current_date . ' ' . sprintf('%02d', $plan_hour) . ':' . sprintf('%02d', $plan_minute) . ':00';

        // SQLクエリを準備
        $stmt = $pdo->prepare('INSERT INTO event (route_id, travel_id, is_transport, charge, start_datetime, end_datetime, transport_id, place, event_detail) VALUES (:route_id, :travel_id, :is_transport, :charge, :start_datetime, :end_datetime, :transport_id, :place, :event_detail)');

        // 値をバインドしてSQLを実行
        $stmt->bindValue(':route_id', $button_number, PDO::PARAM_INT);
        $stmt->bindValue(':travel_id', $travel_id, PDO::PARAM_INT);  // 外部キーとして旅行ID
        $stmt->bindValue(':is_transport', $is_transport, PDO::PARAM_BOOL); // データ型を修正
        $stmt->bindValue(':charge', 0, PDO::PARAM_INT); // この行をSQLクエリに合わせて修正
        $stmt->bindValue(':start_datetime', $plan_datetime, PDO::PARAM_STR);
        $stmt->bindValue(':end_datetime', $plan_datetime, PDO::PARAM_STR);
        $stmt->bindValue(':transport_id', 5, PDO::PARAM_INT); // 仮の transport_id
        $stmt->bindValue(':place', $place, PDO::PARAM_STR);  // データ型を修正
        $stmt->bindValue(':event_detail', $plan_detil, PDO::PARAM_STR);  // データ型を修正

        // SQLを実行してデータを挿入
        $stmt->execute();

        header("Location: addplan.php");
        exit();

    }
} else {
    echo "POSTリクエストがありません。";
}