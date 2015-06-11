<?php
/*
Plugin Name: Russian Currency
Plugin URI: https://wordpress.org/plugins/russian-currency/
Description: Виджет с официальными и биржевыми курсами валют.
Version: 1.00
Author: Flector
Author URI: https://profiles.wordpress.org/flector#content-plugins
*/ 

class Russian_Currency_Widget extends WP_Widget {

	private $m_defaults = array( 
		'title'         => 'Курсы валют',
		'currency-show' => 'all',
		'color'         => '#F5D374',
        'color-number'  => '#000000',
        'color-title'   => '#000000',
		'cash-time'     => 180
	);

	public function __construct() {
		$l_options = array('description' => 'Виджет плагина Russian Currency');
		parent::__construct('russian_currency', 'Виджет плагина Russian Currency', $l_options);
        add_action('admin_enqueue_scripts', array($this, 'rc_enqueue_scripts'));
        add_action('admin_footer-widgets.php', array($this, 'rc_print_scripts' ), 9999);
	}
    
    public function rc_enqueue_scripts($hook_suffix) {
		if ('widgets.php' !== $hook_suffix) {
			return;
		}
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('underscore');
	}
    
    public function rc_print_scripts() {
		?>
		<script>
			(function($){
				function initColorPicker(widget) {
					widget.find('.color-picker' ).wpColorPicker( {
						change: _.throttle(function() { 
							$(this).trigger('change');
						}, 10000 )
					});
				}

				function onFormUpdate(event, widget) {
					initColorPicker(widget);
				}

				$(document).on('widget-added widget-updated', onFormUpdate);

				$( document ).ready( function() {
					$('#widgets-right .widget:has(.color-picker)').each(function () {
						initColorPicker($(this));
					});
				});
			}(jQuery));
		</script>
		<?php
	}

	public function widget($p_args, $p_instance) {
		extract($p_args, EXTR_PREFIX_ALL, 'l_args');
		
		if (!empty($p_instance['title'])) {
			$l_title = $p_instance['title'];
		} else {
			$l_title = 'Курсы валют';
		}
		$l_title = apply_filters('widget_title', $l_title);

		echo $l_args_before_widget;
		echo $l_args_before_title . $l_title . $l_args_after_title;

		$l_tag_params = wp_parse_args($p_instance, $this->m_defaults);
		

		echo '<div class="russian-currency">';

        if ($l_tag_params['currency-show'] == 'cb') {
            Currency_Echo($l_tag_params['color'],$l_tag_params['color-number'],$l_tag_params['color-title'],$l_tag_params['cash-time']);
        } elseif ($l_tag_params['currency-show'] == 'market') {
            Currency_Echo2($l_tag_params['color'],$l_tag_params['color-number'],$l_tag_params['color-title'],$l_tag_params['cash-time']);
        } else {
            Currency_Echo($l_tag_params['color'],$l_tag_params['color-number'],$l_tag_params['color-title'],$l_tag_params['cash-time']);
            Currency_Echo2($l_tag_params['color'],$l_tag_params['color-number'],$l_tag_params['color-title'],$l_tag_params['cash-time']);
        }

		echo '</div>';


		echo $l_args_after_widget;
	}

	public function form($p_instance) {

		$l_instance = wp_parse_args($p_instance, $this->m_defaults);
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '">' .
			'Заголовок:' . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') .
			'" name="' . $this->get_field_name( 'title' ) . '" type="text" ' .
			'value="' . __(esc_attr($l_instance['title']),'russian-currency') . '" />';
		echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id('currency-show') . '">' .
			'Показывать курсы:' . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('currency-show') . 
			'" name="' . $this->get_field_name('currency-show') . '">';
		echo '<option ' . selected('cb', $l_instance['currency-show'], false) .
			' value="cb">Только курсы от ЦБ</option>';
		echo '<option ' . selected('market', $l_instance['currency-show'], false) .
			' value="market">Только биржевые курсы</option>';
		echo '<option ' . selected('all', $l_instance['currency-show'], false) .
			' value="all">И то и другое</option>';
		echo '</select>';
		echo '</p>';
				
				
		echo '<p>';
		echo '<label for="' . $this->get_field_id('cash-time') . '">' .
			'Кэширование данных (в минутах):' . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('cash-time') .
			'" name="' . $this->get_field_name('cash-time') . '" type="text" ' .
			'value="' . esc_attr($l_instance['cash-time']) . '" />';
		echo '</p>';

        
        echo '<p>';
		echo '<label for="' . $this->get_field_id('color') . '">' .
			'Цвет значков валюты: ' . '</label>';
		echo '<br /><input class="widefat color-picker" id="' . $this->get_field_id('color') .
			'" name="' . $this->get_field_name('color') . '" type="text" ' .
			'value="' . esc_attr($l_instance['color']) . '" />';
        
        
		echo '<label for="' . $this->get_field_id('color-number') . '">' .
			'<br />Цвет чисел курса: ' . '</label>';
		echo '<br /><input class="widefat color-picker" id="' . $this->get_field_id('color-number') .
			'" name="' . $this->get_field_name('color-number') . '" type="text" ' .
			'value="' . esc_attr($l_instance['color-number']) . '" />';
        
        
		echo '<label for="' . $this->get_field_id('color-title') . '">' .
			'<br />Цвет надписей: ' . '</label>';
		echo '<br /><input class="widefat color-picker" id="' . $this->get_field_id('color-title') .
			'" name="' . $this->get_field_name('color-title') . '" type="text" ' .
			'value="' . esc_attr($l_instance['color-title']) . '" />';
		echo '</p>';
        
		
	}

	public function update($p_new_instance, $p_old_instance) {

		$l_instance['title'] = strip_tags(stripslashes($p_new_instance['title']));

		if ('cb' == $p_new_instance['currency-show']) {
			$l_instance['currency-show'] = 'cb';
		} else if ('market' == $p_new_instance['currency-show']) {
			$l_instance['currency-show'] = 'market';
		} else if ('all' == $p_new_instance['currency-show']) {
			$l_instance['currency-show'] = 'all';
		} else {
			$l_instance['currency-show'] = $p_old_instance['currency-show'];
		}
		
        
		if (is_numeric($p_new_instance['cash-time'])) {
			$l_instance['cash-time'] = $p_new_instance['cash-time'] + 0;
		} else {
			$l_instance['cash-time'] = $p_old_instance['cash-time'] + 0;
		}
        
		$l_instance['color'] = strip_tags(stripslashes($p_new_instance['color']));
        if (check_color($l_instance['color'])==false){$l_instance['color']=$p_old_instance['color'];}
        
        $l_instance['color-number'] = strip_tags(stripslashes($p_new_instance['color-number']));
        if (check_color($l_instance['color-number'])==false){$l_instance['color-number']=$p_old_instance['color-number'];}
        
        $l_instance['color-title'] = strip_tags(stripslashes($p_new_instance['color-title']));
        if (check_color($l_instance['color-title'])==false){$l_instance['color-title']=$p_old_instance['color-title'];}
        
		
        return $l_instance;
	}	
}


add_action('widgets_init', create_function('', 'register_widget( "Russian_Currency_Widget" );'));

function russian_currency_files() {
	$purl = plugins_url();
	wp_register_style('russian-currency', $purl . '/russian-currency/russian-currency.css');
	wp_enqueue_style('russian-currency');   
}
add_action('wp_enqueue_scripts', 'russian_currency_files');

function check_color($value) { 
    if (preg_match('/^#[a-f0-9]{6}$/i', $value)) {   
        return true;
    }
    return false;
}

function Currency_Echo($color,$color2,$color3,$time){
$rates = "";

$date_1=date('d/m/Y', time()-259200);
$date_2=date('d/m/Y', time()+86400);
$requrl= "http://www.cbr.ru/scripts/XML_dynamic.asp?date_req1={$date_1}&date_req2={$date_2}&VAL_NM_RQ=R01235";
$requrl2= "http://www.cbr.ru/scripts/XML_dynamic.asp?date_req1={$date_1}&date_req2={$date_2}&VAL_NM_RQ=R01239";
$name1 = 'russian_currency_cb_usd';
$name2 = 'russian_currency_cb_eur';

$cached = get_transient($name1);
if ($cached !== false) {
   $doc = unserialize($cached);}
else {
   $doc = @file($requrl);
   if (!$doc) {$time=1;}
   set_transient($name1, serialize($doc), 60 * $time);
}
 
if (!empty($doc)) { 
$doc = implode($doc, '');
$r = array();
    if(preg_match("/<ValCurs.*?>(.*?)<\/ValCurs>/is", $doc, $m))
        preg_match_all("/<Record(.*?)>(.*?)<\/Record>/is", $m[1], $r, PREG_SET_ORDER);
$m = array(); $d = array();

for($i=0; $i<count($r); $i++) {
	if(preg_match("/Date=\"(\d{2})\.(\d{2})\.(\d{4})\"/is", $r[$i][1],$m)) {
		$dv = "{$m[1]}/{$m[2]}/{$m[3]}";
		if(preg_match("/<Nominal>(.*?)<\/Nominal>.*?<Value>(.*?)<\/Value>/is", $r[$i][2], $m)) {
			$m[2] = preg_replace("/,/",".",$m[2]);
			$d[] = array($dv, $m[1], $m[2]);
			}
		}
	}
$last = array_pop($d);
$prev = array_pop($d);
$date = $last[0];
$rate = sprintf("%.2f",$last[2]);
$rates['usd'] = $rate;
}
 
$cached = get_transient($name2);
if ($cached !== false) {
   $doc = unserialize($cached);}
else {
   $doc = @file($requrl2);
   if (!$doc) {$time=1;}
   set_transient($name2, serialize($doc), 60 * $time);
}

if (!empty($doc)) { 
$doc = implode($doc, '');
$r = array();
    if(preg_match("/<ValCurs.*?>(.*?)<\/ValCurs>/is", $doc, $m))
        preg_match_all("/<Record(.*?)>(.*?)<\/Record>/is", $m[1], $r, PREG_SET_ORDER);
$m = array(); $d = array();

for($i=0; $i<count($r); $i++) {
	if(preg_match("/Date=\"(\d{2})\.(\d{2})\.(\d{4})\"/is", $r[$i][1],$m)) {
		$dv = "{$m[1]}/{$m[2]}/{$m[3]}";
		if(preg_match("/<Nominal>(.*?)<\/Nominal>.*?<Value>(.*?)<\/Value>/is", $r[$i][2], $m)) {
			$m[2] = preg_replace("/,/",".",$m[2]);
			$d[] = array($dv, $m[1], $m[2]);
			}
		}
	}
$last = array_pop($d);
$prev = array_pop($d);
$date = $last[0];
$rate = sprintf("%.2f",$last[2]);
$rates['eur'] = $rate;
}

    if ((empty($rates['usd'])) or (empty($rates['eur']))) {
        $rc_options = get_option('rc_options');
        $rates['usd'] = $rc_options['usd_cb'];
        $rates['eur'] = $rc_options['eur_cb'];
    } else {
        $rc_options = get_option('rc_options');
        $rc_options['usd_cb'] = $rates['usd'];
        $rc_options['eur_cb'] = $rates['eur'];
        update_option('rc_options', $rc_options);
    }
 
echo '
<table id="currency" cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr><td class="curname" style="color:'. $color3 . ';" colspan="2">Курс ЦБ</td></tr>
<tr>

<td>
<table cellspacing="0" cellpadding="0">
<tbody><tr>
<td><span class="znak" style="color:'. $color . ';">$</span></td>
<td>&nbsp;</td><td class="number" style="color:'. $color2 . ';">'.substr($rates['usd'],0,5).'</td>
</tr></tbody>
</table>
</td>

<td style="padding-left:10px;">
<table cellspacing="0" cellpadding="0">
<tbody><tr><td><span class="znak" style="color:'. $color . ';">&euro;</span></td>
<td>&nbsp;</td><td class="number" style="color:'. $color2 . ';">'.substr($rates['eur'],0,5).'</td></tr>
</tbody></table>
</td>

</tr>
</tbody>
</table> ';}
 
function Currency_Echo2($color,$color2,$color3,$time){

echo '
<table id="currency2" cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr><td class="curname" style="color:'. $color3 . ';" colspan="2">Биржевой курс</td></tr>
<tr>

<td>
<table cellspacing="0" cellpadding="0">
<tbody><tr>
<td><span class="znak" style="color:'. $color . ';">$</span></td>
<td>&nbsp;</td><td class="number" style="color:'. $color2 . ';">'.convertCurrencyUnit("USD", "RUB", '1', $time).'</td>
</tr></tbody>
</table>
</td>

<td style="padding-left:10px;">
<table cellspacing="0" cellpadding="0">
<tbody><tr><td><span class="znak" style="color:'. $color . ';">&euro;</span></td>
<td>&nbsp;</td><td class="number" style="color:'. $color2 . ';">'.convertCurrencyUnit2("EUR", "RUB", '1', $time).'</td>
</tr></tbody>
</table>
</td>

</tr>
</tbody>
</table> ';}
 
function convertCurrencyUnit($from_Currency, $to_Currency, $unit_amount = 1, $time) {

    $url = 'http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%3D%22' . $from_Currency . $to_Currency . '%22&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
    $name = 'russian_currency_market';
    
    $cached = get_transient($name);
        if ($cached !== false) {
            $rawdata = unserialize($cached);}
        else {
            $rawdata = @file_get_contents($url);
            if (!$rawdata) {$time=1;}
            set_transient($name, serialize($rawdata), 60 * $time);
        }
    
    if (!empty($rawdata)) {
    $decodedArray = json_decode($rawdata, true);
    $converted_unit_amount = $decodedArray['query']['results']['rate']['Rate'];
    }
    
    if (!empty($rawdata)) {
        $rc_options = get_option('rc_options');
        $rc_options['usd_market'] = $converted_unit_amount;
        update_option('rc_options', $rc_options);
    } else {
        $rc_options = get_option('rc_options');
        $converted_unit_amount = $rc_options['usd_market'];
    }
    
    return substr($converted_unit_amount * $unit_amount,0,5);
} 
  
function convertCurrencyUnit2($from_Currency, $to_Currency, $unit_amount = 1, $time) {

    $url = 'http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%3D%22' . $from_Currency . $to_Currency . '%22&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
    $name = 'russian_currency_market2';

    $cached = get_transient($name);
        if ($cached !== false) {
            $rawdata = unserialize($cached);}
        else {
            $rawdata = @file_get_contents($url);
            if (!$rawdata) {$time=1;}
            set_transient($name, serialize($rawdata), 60 * $time);
        }
    
    if (!empty($rawdata)) {
    $decodedArray = json_decode($rawdata, true);
    $converted_unit_amount = $decodedArray['query']['results']['rate']['Rate'];
    }
    
    if (!empty($rawdata)) {
        $rc_options = get_option('rc_options');
        $rc_options['eur_market'] = $converted_unit_amount;
        update_option('rc_options', $rc_options);
    } else {
        $rc_options = get_option('rc_options');
        $converted_unit_amount = $rc_options['eur_market'];
    }
    
    return substr($converted_unit_amount * $unit_amount,0,5);
} 
 
?>