<?php
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 

if (count($references)):
    $queryType = get_option('reference_query_type') == 'contains' ? 'contains' : 'is+exactly';
    // Dublin Core Title is always 50.
    $referenceId = $slugData['type'] == 'Element' ? $slugData['id'] : 50;
    // Prepare and display skip links.
    if ($options['skiplinks']):
        // Get the list of headers.
        $letters = array('number' => false) + array_fill_keys(range('A', 'Z'), false);
        foreach ($references as $reference => $referenceData):
        if (strpos($reference, '{') !== false) {
            $reference = json_decode($reference);
            $firstname = $reference->{'first'};
            $middlename = $reference->{'middle'};
            $lastname = $reference->{'last'};
            $referencelabel = $lastname . ', ' . $firstname . ' ' . $middlename;
            $reference = $firstname . ' ' . $middlename . ' ' . $lastname;
        }   
            $first_char = function_exists('mb_substr') ? mb_substr($reference, 0, 1) : substr($reference, 0, 1);
            if (strlen($first_char) == 0 || preg_match('/\W|\d/u', $first_char)):
                $letters['number'] = true;
            else:
                $letters[strtoupper($first_char)] = true;
            endif;
        endforeach;
        $pagination_list = '<ul class="pagination_list">';
        foreach ($letters as $letter => $isSet):
            $letterDisplay = $letter == 'number' ? '#0-9' : $letter;
            if ($isSet):
                $pagination_list .= sprintf('<li class="pagination_range"><a href="' . WEB_ROOT . '/references/' . $slug . '?Head%s">%s</a></li>', $letter, $letterDisplay);
            else:
                $pagination_list .= sprintf('<li class="pagination_range"><span>%s</span></li>', $letterDisplay);
            endif;
        endforeach;
        $pagination_list .= '</ul>';
    ?>

<div class="pagination reference-pagination" id="pagination-top">
    <?php echo $pagination_list; ?>
</div>
    <?php endif; ?>

<div id="reference-headings">
    <?php
    $linkSingle = (boolean) get_option('reference_link_to_single');
    $current_heading = '';
    $current_id = '';
    foreach ($references as $reference => $referenceData):
    if (strpos($reference, '{') !== false) {
        $reference = json_decode($reference);
        $firstname = $reference->{'first'};
        $middlename = $reference->{'middle'};
        $lastname = $reference->{'last'};
        $referencelabel = $lastname . ', ' . $firstname . ' ' . $middlename;
        $reference = $firstname . ' ' . $middlename . ' ' . $lastname;
        }      
        // Add the first character as header if wanted.
        if ($options['headings']):
            
            if ($referencelabel):
            $first_char = function_exists('mb_substr') ? mb_substr($referencelabel, 0, 1) : substr($referencelabel, 0, 1);
            else:
            $first_char = function_exists('mb_substr') ? mb_substr($reference, 0, 1) : substr($reference, 0, 1);
                endif;
                
            if (strlen($first_char) == 0 || preg_match('/\W|\d/u', $first_char)) {
                $first_char = '#0-9';
            }
            $current_first_char = strtoupper($first_char);
            
            if (strpos($actual_link, 'Head') !== false) {
            $last = substr($actual_link, -1); 
            } elseif (strpos($actual_link, 'Head') !== true) {
            $last = 'number';
            }  
            if ($first_char === $last):
            if ($current_heading !== $current_first_char):
                $current_heading = $current_first_char;
                $current_id = $current_heading === '#0-9' ? 'number' : $current_heading;
    ?>
            
    <h3 class="reference-heading"><?php echo $current_heading; ?></h3>

    <?php
            endif;
            endif;
            endif;
        
        if ($first_char === $last):

    ?>

    <p class="reference-record">
        <?php if (empty($options['raw'])):
            if ($linkSingle && $referenceData['count'] === 1):
                $record = get_record_by_id('Item', $referenceData['record_id']);
                if ($referencelabel):
                echo link_to($record, null, $referencelabel);
                else:
                echo link_to($record, null, $reference);
                    endif;
            else:
                $url = 'items/browse?';
                if ($slugData['type'] == 'ItemType'):
                    $url .= 'type=' . $slugData['id'] . '&amp;';
                endif;
                
               // search?query="Rankin+C.+Blount"&query_type=keyword 
                if ($referencelabel):
                    if (!$middlename):
                    $url = WEB_ROOT . '/search?query=' . $firstname . '+' . $lastname . '&query_type=exact_match';
                elseif (!lastname):
                    $url = WEB_ROOT . '/search?query=' . $firstname . '+' . $middlename . '&query_type=exact_match';
                elseif (!firstname):
                    $url = WEB_ROOT . '/search?query=' . $middlename . '+' . $lastname . '&query_type=exact_match';
                else:
                    $url = WEB_ROOT . '/search?query=' . $firstname . '+' . $middlename . '+' . $lastname . '&query_type=exact_match';
                endif;

                echo '<a href="' . $url . '">' . $referencelabel . '</a>';
                    else:
                    $url .= sprintf('advanced[0][element_id]=%s&amp;advanced[0][type]=%s&amp;advanced[0][terms]=%s',
                    $referenceId, $queryType, urlencode($reference));
                echo '<a href="' . url($url) . '">' . $reference . '</a>';
                        endif;
                
                // Can be null when references are set directly.
                if ($referenceData['count']):
                    echo ' (' . $referenceData['count'] . ')';
                endif;
            endif;
        else:
            echo $reference;
        endif; ?>
    </p>
    
    <?php
    else:
        echo "";
        endif;
   

    ?>
    
    
    <?php endforeach; ?>
</div>

    <?php if ($options['skiplinks']): ?>
<div class="pagination reference-pagination" id="pagination-bottom">
    <?php echo $pagination_list; ?>
</div>
    <?php endif;
endif;
