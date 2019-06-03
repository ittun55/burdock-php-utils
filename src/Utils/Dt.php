<?php

class Dt
{
    public static function alignYyyyMmDd($str) {
        list($date, $time) = explode(' ', $str);
        return date('Y-m-d', strtotime($date));
    }

    public static function convGtJDate($src, $week=false, $yyyy=false)
    {
        if (!$src) return '';

        $jp_week = ['日', '月', '火', '水', '木', '金', '土'];
        list($year, $month, $day) = explode("-", self::alignYyyyMmDd($src));
        if (!@checkdate($month, $day, $year) || $year < 1869 || strlen($year) !== 4
            || strlen($month) !== 2 || strlen($day) !== 2) return false;
        $date = $year.$month.$day;
        if ($date >= 20190501) {
            $gengo = "令和";
            $wayear = $year - 2018;
        } elseif ($date >= 19890108) {
            $gengo = "平成";
            $wayear = $year - 1988;
        } elseif ($date >= 19261225) {
            $gengo = "昭和";
            $wayear = $year - 1925;
        } elseif ($date >= 19120730) {
            $gengo = "大正";
            $wayear = $year - 1911;
        } else {
            $gengo = "明治";
            $wayear = $year - 1868;
        }
        if ($yyyy === true) {
            $wayear = 0;
        }
        switch ($wayear) {
            case 0:
                $wadate = $year."年".$month."月".$day."日";
                break;
            case 1:
                $wadate = $gengo."元年".$month."月".$day."日";
                break;
            default:
                $wadate = $gengo.sprintf("%02d", $wayear)."年".$month."月".$day."日";
        }
        if ($week) {
            $w = date('w', strtotime($src));
            $wadate .= '('.$jp_week[$w].')';
        }
        return $wadate;
    }

    public static function japan_holiday_ics() {
        // カレンダーID
        $calendar_id = urlencode('japanese__ja@holiday.calendar.google.com');
        $url = 'https://calendar.google.com/calendar/ical/'.$calendar_id.'/public/full.ics';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        if (!empty($result)) {
            $items = $sort = array();
            $start = false;
            $count = 0;
            foreach(explode("\n", $result) as $row => $line) {
                // 1行目が「BEGIN:VCALENDAR」でなければ終了
                if (0 === $row && false === stristr($line, 'BEGIN:VCALENDAR')) {
                    break;
                }
                // 改行などを削除
                $line = trim($line);
                // 「BEGIN:VEVENT」なら日付データの開始
                if (false !== stristr($line, 'BEGIN:VEVENT')) {
                    $start = true;
                } elseif ($start) {
                    // 「END:VEVENT」なら日付データの終了
                    if (false !== stristr($line, 'END:VEVENT')) {
                        $start = false;
                        // 次のデータ用にカウントを追加
                        ++$count;
                    } else {
                        // 配列がなければ作成
                        if (empty($items[$count])) {
                            $items[$count] = array('date' => null, 'title' => null);
                        }
                        // 「DTSTART;～」（対象日）の処理
                        if(0 === strpos($line, 'DTSTART;VALUE')) {
                            $date = explode(':', $line);
                            $date = end($date);
                            $items[$count]['date'] = $date;
                            // ソート用の配列にセット
                            $sort[$count] = $date;
                        }
                        // 「SUMMARY:～」（名称）の処理
                        elseif(0 === strpos($line, 'SUMMARY:')) {
                            list($title) = explode('/', substr($line, 8));
                            $items[$count]['title'] = trim($title);
                        }
                    }
                }
            }
            // 日付でソート
            $items = array_combine($sort, $items);
            ksort($items);
            return $items;
        }
    }
}
