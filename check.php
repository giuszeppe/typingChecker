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

    //$userText = explode(' ', preg_replace('/\s+/', ' ', htmlspecialchars($_POST['userText'])));
    //$staticText = explode(' ', preg_replace('/\s+/', ' ', htmlspecialchars($_POST['staticText'])));

    /*
    $wrongWord = 0;
    $goodWord = 0;

    $missingWord = count($userText) - count($staticText);
    $appoggio = $missingWord;

    $missingOffset = 0;
    $extraOffset = 0;

    //fixed
    $K = 1;
    //Weighted comparison constant, you can increase or descrease this to modify performance.
    $UPPER_BOUND = 80;
    $LOWER_BOUND = 30;


    for ($i = 0; $i < count($staticText); $i++) {
        $rightWord = $staticText[$i];
        $userWord = $userText[$i + $extraOffset];
        $difference = strlen($userWord) - strlen($rightWord);
        $lenghtDifference = $difference > 0 ? $difference : ($difference < 0 ? 1 / abs($difference) : 1);

        $weight = $K / $lenghtDifference;

        if ($rightWord == $userWord) {
            $goodWord++;
        } else {
            similar_text(strtoupper($rightWord), strtoupper($userWord), $percent);

            $weightedPercent = $weight * 55;
            $weightedPercent = $weightedPercent >= $UPPER_BOUND ? $UPPER_BOUND : ($weightedPercent <= $LOWER_BOUND ? $LOWER_BOUND : $weightedPercent);

            if ($percent > $weightedPercent || $appoggio == 0) {
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
    $i = $i + $extraOffset;
    for ($i; $i < count($userText); $i++) {
        $userText[$i] = '<span class="extra_word">' . strip_tags($userText[$i]) . '</span>';
    }
    */
    function diff($old, $new)
    {
        $matrix = array();
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) return array(array('d' => $old, 'i' => $new));
        return array_merge(
            diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
        );
    }
    /*
    function check_extra_missing_wrong($k): string
    {
        $ret = '';
        $percent = 0;

        $words_d = preg_split("/[\s]+/", $k['d']);
        $words_i = preg_split("/[\s]+/", $k['i']);

        $dOffset = 0;
        $iOffset = 0;
        for ($i = 0; $i < count($words_d[$i]); $i++) {
            $d_word = $words_d[$i + $dOffset];
            similar_text(strtoupper($words_d[$i]), strtoupper($words_i[$i], $percent));
            if ($percent < 20) {
                $ret .= "<span style=\"color:blue\">" . implode(' ', $k['i']) . "</span> ";
            } else {

                $ret .= "<span style=\"color:red\">" . implode(' ', $k['i']) . "</span> ";
            }
        }
        for ($i; $i < count($words_i[$i]); $i++) {
        }
        return $ret;
    }
    */




    function htmlDiff($old, $new)
    {
        $sortArr = function ($arr) {
            return rsort($arr);
        };
        $missingWord = 0;
        $extraWord = 0;
        $goodWord = 0;
        $wrongWord = 0;
        $percent = 0;
        
        $ret = [];
        $diff = diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
        foreach ($diff as $k) {
          $extraSet = 0;
          $wrongSet = 0;
          $percents = [];
            $missCount = 0;
            $extraCount = 0;
            if (is_array($k)) {
                if (!empty($k['d'])) {
                    $missCount = count($k['d']);
                    if (empty($k['i'])) {

                        array_push($ret, "<span style='color:orange'>" . implode(' ', $k['d']) . "</span> ");
                    }
                }
                if (!empty($k['i'])) {
                    $extraCount = count($k['i']);
                    if (empty($k['d'])) {

                        array_push($ret, "<span style='color:blue'>" . implode(' ', $k['i']) . "</span> ");
                    } else {
                      
                        $extraNumber = 0;
                        $wrongNumber = 0;
                        
                        // altro approccio: faccio un confronto 1 a tutti e seleziono come errate quelle con la percentuale di similitudine piu alta
                        if ($missCount - $extraCount < 0) {
                            $extraNumber = $extraCount - $missCount;
                        }
                        if ($extraCount - $missCount > 0) {
                            $wrongNumber = $missCount - $extraCount;
                        }
                        

                        
                      //echo 'extracount: '.$extraCount . ' misscount: ' . $missCount . ' extraNumber: ' .$extraNumber. ' wrongNumber: '. $wrongNumber . '        ';

                        

                        /*$k['i'] contiene la stringa inserita dall'utente nel formato               ['cia', 'dio']
                          $percents contiene la percentuale di similitudine della parola 
                          inserita dall'utente con quella originale nel formato
                          [[60,50,40,90],[40,50,70,80]]
                            parola 1      parola 2

                        */
                        for ($i = 0; $i < count($k['i']); $i++) {
                            //echo $i;
                            
                            $percent = 0;
                            $percents[$k['i'][$i]] = [];
                            for ($j = 0; $j < count($k['d']); $j++) {
                                similar_text($k['i'][$i], $k['d'][$j], $percent);
                                $percents[$k['i'][$i]][$k['d'][$j]] = $percent;
                                
                            }
                            //var_dump($wrongSet $wrongNumber) ;
                           
                            if($extraNumber == 0){
                              array_push($ret, "<span style='color:red'>" . $k['i'][$i] . "</span> ");
                            } elseif($wrongNumber == 0){
                              array_push($ret, "<span style='color:blue'>" . $k['i'][$i] . "</span> ");
                              $extraSet++;
                            } else {
                              krsort($percents[$k['i'][$i]]);
                              $max = max($percents[$k['i'][$i]]);
                              $keys = array_keys($percents);
                              
                              if($wrongSet != $missCount){
                                foreach($keys as $key){
                                  $mostSimilarWord = false;
                                  $maxWords = array_keys($percents[$key],$max);
                                  $mostSimilarWord = array_keys($percents,[$maxWords[0] => $max])[0];
                                 // print_r(array_keys($percents,[$maxWords[0] => $max])[0]);
                                  //print_r($percents);

                                  
                                  if($mostSimilarWord){
                                    array_push($ret, "<span style='color:red'>" . $mostSimilarWord . "</span> ");
                                    $wrongSet++;
                                  } else{
                                    array_push($ret, "<span style='color:blue'>" . $k['i'][$i] . "</span> ");
                                    $extraSet++;
                                  }
                                } 
                              }else {
                                array_push($ret, "<span style='color:blue'>" . $k['i'][$i] . "</span> ");
                              }
                            }
                        }

                    }
                }

                if ($missCount - $extraCount < 0) {
                    $extraWord += $extraCount - $missCount;
                    $wrongWord += $missCount;
                } elseif ($extraCount - $missCount < 0) {
                    $missingWord += $missCount - $extraCount;
                } else {
                    $wrongWord += count($k['d']);
                }
            } else {
                array_push($ret, $k . ' ');
                $goodWord = $goodWord + 1;
            }
        }
        return ['ret' => $ret, 'goodWord' => $goodWord, 'extraWord' => $extraWord, 'missingWord' => $missingWord, 'wrongWord' => $wrongWord];
    }

    $staticText = preg_replace('/\s+/', ' ', $_POST['staticText']);
    //$staticText = $_POST['staticText'];
    $userText = $_POST['userText'];

    $result = htmlDiff($_POST['staticText'], $_POST['userText']);


    header('Content-type: application/json');
    echo json_encode([
        'startingText' => explode(' ', $staticText),
        'inputText' => $result['ret'],
        'wrongWords' => $result['wrongWord'],
        'goodWords' => $result['goodWord'],
        'missingWords' => $result['missingWord'],
        'extraWords' => $result['extraWord']
    ]);
}
