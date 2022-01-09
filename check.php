<?php

function strip_tags_content($text, $tags = '', $invert = FALSE)
{

    preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
    $tags = array_unique($tags[1]);

    if (is_array($tags) and count($tags) > 0) {
        if ($invert == FALSE) {
            return preg_replace('@<(?!(?:' . implode('|', $tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
        } else {
            return preg_replace('@<(' . implode('|', $tags) . ')\b.*?>.*?</\1>@si', '', $text);
        }
    } elseif ($invert == FALSE) {
        return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
    }
    return $text;
}

if (isset($_POST['userText']) && isset($_POST['staticText'])) {

    $userText = explode(' ', preg_replace('/\s+/', ' ', htmlspecialchars($_POST['userText'])));
    $staticText = explode(' ', preg_replace('/\s+/', ' ', htmlspecialchars($_POST['staticText'])));


    $wrongWord = 0;
    $goodWord = 0;

    $missingWord = count($userText) - count($staticText);
    $appoggio = $missingWord;

    $missingOffset = 0;
    $extraOffset = 0;


    for ($i = 0; $i < count($staticText); $i++) {
        $rightWord = $staticText[$i];
        $userWord = $userText[$i + $extraOffset];
        //echo $i;
        //echo $rightWord;
        //echo $userWord;

        if ($rightWord == $userWord) {
            $goodWord++;
        } else {
            similar_text(strtoupper($rightWord), strtoupper($userWord), $percent);

            if ($percent > 65 || $appoggio == 0) {
                $wrongWord++;
                $userText[$i + $extraOffset] = '<span>' . $userWord . '</span>';
            } elseif ($appoggio < 0) {
                //$i--;
                $missingOffset++;
                array_splice($userText, $i, 0, '<span class="missing_text">' . strip_tags($rightWord) . '</span>');
                $appoggio++;
            } elseif ($appoggio > 0) {
                $userText[$i + $extraOffset] = '<span class="extra_word">' . strip_tags($userWord) . '</span>';
                $extraOffset++;
                $appoggio--;
                $i -= 1;
            }
        }
    }
    $i = $i++ + $extraOffset;
    for ($i; $i < count($userText); $i++) {
        $userText[$i] = '<span class="extra_word">' . strip_tags($userText[$i]) . '</span>';
    }

    header('Content-type: application/json');
    echo json_encode([
        'startingText' => $staticText,
        'inputText' => $userText,
        'wrongWords' => $wrongWord,
        'goodWords' => $goodWord,
        'missingWords' => $missingWord,
    ]);
}
