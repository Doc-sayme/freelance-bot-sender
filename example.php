<?php

    session_start();

    require_once('freelance-bot-sender.php');

    $freelance_bot_sender = new freelance_bot_sender;
        
        //set login data
            $freelance_bot_sender ->login = 'LOGIN';
            $freelance_bot_sender ->password = 'PASSWORD';

        //set user cookies
            if( !isset( $_SESSION['freelance_bot_session_cookies'] ) )
                $_SESSION['freelance_bot_session_cookies'] = $freelance_bot_sender ->auth();

            $freelance_bot_sender ->session_cookies = $_SESSION['freelance_bot_session_cookies'];
           

            if( isset( $_POST['offer'] ) ){
                
                //Where send comment; array();
                    $ids = $freelance_bot_sender ->get_ids(); // - at 1 page;  get_url_order(TRUE) - all list at all pages

                $cost = $_POST['cost'];
                $term = $_POST['term'];
                $msg_body = $_POST['msg_body'];
                $use_signature = isset( $_POST['use_signature'] ) ? $_POST['use_signature'] : FALSE;
                $need_prepay = isset( $_POST['need_prepay'] ) ? $_POST['need_prepay'] : FALSE;
                
                var_dump( $freelance_bot_sender ->send_comment( $ids[0], $cost, $term, $msg_body, $use_signature, $need_prepay ) );

            }
?>

    <form method="POST" action="" enctype="application/x-www-form-urlencoded">
        <label for="cost_from">Стоимость (рублей)</label>
        <input type="text" name="cost" id="cost_from" value="" size="10" maxlength="8">
        <br/>
        <label for="time_from">Выполню за (дней)</label>
        <input id="time_from" type="text" name="term" value="" size="10" maxlength="3">
        <br/>
        <label for="ps_text">Текст</label>
        <textarea name="msg_body" id="ps_text"></textarea>
        <br/>
        <label><input type="checkbox" name="use_signature"><span>Вставить подпись</span></label>
        <br/>
        <label><input type="checkbox" name="need_prepay"><span>Работаю по предоплате или безопасной сделке</span></label>
        <br/>
        <button name="offer" type="submit">Опубликовать ответ</button>
    </form>