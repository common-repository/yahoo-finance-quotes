<?php

/*
Plugin Name: Yahoo Finance Quotes
Plugin URI: http://www.metastocktradingsystem.com/yahoo-finance-quotes-wordpress-plugin/
Description: Manage and share your selected quotes (indices, stocks, etc) from yahoo finance on wordpress blog.
Author: Taro Hideyoshi
Version: 0.1.0
Author URI: http://www.metastocktradingsystem.com/
*/

/*
Yahoo Finance Quotes is a wordpress plugin that allows you to manage and display your selected quotes (indices, stocks, etc) from yahoo finance on your wordpress blog.
Copyright (C) 2010 Taro Hideyoshi

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/
?>

<?php

define('YFQ_TABLE', 'yahoo_finance_quotes');


// run when plugin is activated
register_activation_hook(__FILE__,'yfq_activation');

function yfq_activation() {

    global $wpdb;
	$table_name = $wpdb->prefix . YFQ_TABLE;

	$tables = $wpdb->get_results("show tables;");

	$table_exists = false;

	foreach ( $tables as $table )
	{
		foreach ( $table as $value )
		{
			if ( $value == $table_name )
			{
				$table_exists = true;
				break;
			}
		}
	}

	if ( !$	$table_exists ) {
		$sql = "CREATE TABLE " . $table_name . " (
					yfq_id INT(11) NOT NULL AUTO_INCREMENT,
					yfq_symbol TEXT NOT NULL,
					yfq_category TEXT,
					PRIMARY KEY ( yfq_id )
				)";
		$wpdb->get_results($sql);

		// add default yahoo url to db
		$sql_yahoo_url = "INSERT INTO " . $table_name . " (yfq_symbol, yfq_category)
								VALUES ('http://download.finance.yahoo.com/d/quotes.csv', 'yahoo_url')";
		$wpdb->get_results($sql_yahoo_url);
		
		// add default yahoo parameters to db
		$sql_yahoo_param = "INSERT INTO " . $table_name . " (yfq_symbol, yfq_category)
								VALUES ('&f=snl1c1&e=.csv', 'yahoo_param')";
		$wpdb->get_results($sql_yahoo_param);

		// add enable (default) powered by link
		$sql_yahoo_param = "INSERT INTO " . $table_name . " (yfq_symbol, yfq_category)
								VALUES ('yes', 'enable_powered_by')";
		$wpdb->get_results($sql_yahoo_param);
		
				
	}

}

// only for administrator
if ( is_admin() ){

// add link to admin menu
add_action('admin_menu', 'yfq_admin_menu');

function yfq_admin_menu() {
add_options_page('Yahoo Finance Quotes', 'Yahoo Finance Quotes', 'administrator',
'yahoo-finance-quotes', 'yfq_admin_page');
}
}

function yfq_admin_page() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . YFQ_TABLE;

	$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
	$yfq_id = !empty($_REQUEST['yfq_id']) ? $_REQUEST['yfq_id'] : '';
	$yfq_symbol = !empty($_REQUEST['yfq_symbol']) ? $_REQUEST['yfq_symbol'] : '';
	$yfq_category = !empty($_REQUEST['yfq_category']) ? $_REQUEST['yfq_category'] : '';

	switch($action) {
		case 'add_quote' : 
			
			if( !empty($yfq_symbol) ) {
				$sql = "INSERT INTO " . $table_name . " (yfq_symbol, yfq_category)
								VALUES ('" . $yfq_symbol . "', '" . $yfq_category . "')";

				$wpdb->get_results($sql);
			}

			break;
		case 'update_quote' : 

			if( !empty($yfq_id) && !empty($yfq_symbol) ) {
				$sql = "UPDATE " . $table_name . " SET yfq_symbol='" . $yfq_symbol . "', yfq_category='" . $yfq_category . "' WHERE yfq_id=" . $yfq_id . ";";
				$wpdb->get_results($sql);
			}

			break;
		
		case 'delete_quote' :
			
			if( !empty($yfq_id)) {
				$sql = "DELETE FROM " . $table_name . " WHERE yfq_id=" . $yfq_id . ";";
				$wpdb->get_results($sql);
			}

			break;

		case 'update_yahoo_url' :
			
			if( !empty($yfq_symbol) ) {
				$sql = "UPDATE " . $table_name . " SET yfq_symbol='" . $yfq_symbol . "' WHERE yfq_category='yahoo_url';";
				$wpdb->get_results($sql);
			}

			break;

		case 'update_powered_by' :
			
			if( !empty($yfq_symbol) ) {
				$sql = "UPDATE " . $table_name . " SET yfq_symbol='" . $yfq_symbol . "' WHERE yfq_category='enable_powered_by';";
				$wpdb->get_results($sql);
			}

			break;

	}

	$yahoo_url = $wpdb->get_var($wpdb->prepare("SELECT yfq_symbol FROM " . $table_name . " WHERE yfq_category='yahoo_url';"));
	$powered_by_link = $wpdb->get_var($wpdb->prepare("SELECT yfq_symbol FROM " . $table_name . " WHERE yfq_category='enable_powered_by';"));

?>
	<div class="wrap">
        <h2>Yahoo Finance Quotes</h2>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=yahoo-finance-quotes.php' ?>">
			<?php wp_nonce_field('update-options'); ?>

			<table class="widefat">
				<tbody>
					<tr>
						<th width="50" scope="row">Yahoo URL</th>
						<td width="450" align="left">
							<input size="50" name="yfq_symbol" type="text" id="text_yahoo_url" value="<?php echo $yahoo_url ?>" />
							<input type="hidden" name="action" value="update_yahoo_url">
							<input type="submit" value="<?php _e('Save') ?>" />
							(Do not need change in most cases !)
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<br />
		<div class="wrap">
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=yahoo-finance-quotes.php' ?>">
			<?php wp_nonce_field('update-options'); ?>

			<table class="widefat">
				<tbody>
					<tr>
						<th width="50" scope="row">Display Powered by link</th>
						<td width="450" align="left">
							<?php if( $powered_by_link == 'yes') { ?>
							<input size="50" name="yfq_symbol" type="radio" id="text_powered_yes" value="yes" checked/> yes
							<input size="50" name="yfq_symbol" type="radio" id="text_powered_no" value="no" /> no 
							<?php } else { ?>
							<input size="50" name="yfq_symbol" type="radio" id="text_powered_yes" value="yes" /> yes
							<input size="50" name="yfq_symbol" type="radio" id="text_powered_no" value="no" checked/> no 
							<?php } ?>
							<input type="hidden" name="action" value="update_powered_by">
							<input type="submit" value="<?php _e('Save') ?>" />
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<br />
		<div class="wrap">
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=yahoo-finance-quotes.php' ?>">
			<?php wp_nonce_field('update-options'); ?>

			<table class="widefat">
				<tbody>
					<tr>
						<?php if($action == 'edit') { 
							$stocks = $wpdb->get_results("SELECT yfq_id, yfq_symbol, yfq_category FROM " . $table_name . " WHERE yfq_id = " . $yfq_id . ";" );
						?>
							<th width="50" scope="row">Edit quote</th>
						<?php } else { ?>
							<th width="50" scope="row">Add new quote</th>
						<?php } ?>
						<td width="450" align="left">

						<?php if($action == 'edit') { ?>
							Symbol : <input size="10" name="yfq_symbol" type="text" id="text_quote" value="<?php echo $stocks[0]->yfq_symbol ?>"/>
							Category : <input size="10" name="yfq_category" type="text" id="text_category" value="<?php echo $stocks[0]->yfq_category ?>" />
						<?php } else { ?>
							Symbol : <input size="10" name="yfq_symbol" type="text" id="text_quote" />
							Category : <input size="10" name="yfq_category" type="text" id="text_category" />
						<?php } ?>
						
						<?php if($action == 'edit') { ?>
						    <input type="hidden" name="yfq_id" value="<?php echo $yfq_id ?>" />
							<input type="hidden" name="action" value="update_quote" />
							<input type="submit" value="<?php _e('Save') ?>" />
						<?php } else { ?>
						    <input type="hidden" name="action" value="add_quote" />
							<input type="submit" value="<?php _e('Add') ?>" />
						<?php } ?>						
							
						</td>
					</tr>
					<tr>
						<td width="500" align="left" colspan="2">
							<strong>Symbol</strong> is an existed yahoo quote symbol e.g. ^DJI, MSFT, GOOG and etc. <br /><br />
							<strong>Category</strong> can be anything you define. It uses to indicate the quotes that are displayed as the following examples.
							<br />
							1. [yfq#] or yfq_output() - this will display the quotes in category ''. <br />
							2. [yfq#World Indices] or yfq_output('World Indices' ) - this will display the quotes in category 'World Indices'.
							<br /><br />
							***You can display your quotes by using of the following 2 methods.<br />

                            <strong>1. Put <em>&lt;?php yfq_output('category name') ?&gt;</em> in wordpress template.</strong><br />
							<strong>2. Put <em>&#91;yfq#category name&#93;</em> in your blog content.</strong>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<br />
	<div class="wrap">
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><div style="text-align: center">Symbol</div></th>
					<th scope="col">Category</th>
					<th colspan="2" style="text-align: center">Action</th>
				</tr>
			</thead>

			<tbody>

			<?php

			$stocks = $wpdb->get_results("SELECT yfq_id, yfq_symbol, yfq_category FROM " . $table_name . " WHERE yfq_category NOT LIKE 'yahoo_%' AND yfq_category NOT LIKE 'enable_powered_by%' ORDER BY yfq_category ASC, yfq_id ASC;" );

			foreach ( $stocks as $stock ) {
				$class = ('alternate' == $class) ? '' : 'alternate';
			?>

				<tr class='<?php echo $class; ?>'>
					<th scope="row" style="text-align: center"><?php echo $stock->yfq_symbol; ?></th>
					<td><?php echo $stock->yfq_category; ?></td>
					<td style="text-align: center"><a href="options-general.php?page=yahoo-finance-quotes&amp;action=edit&amp;yfq_id=<?php echo $stock->yfq_id; ?>" class="delete"><?php echo __('Edit'); ?></a></td>
					<td style="text-align: center"><a href="options-general.php?page=yahoo-finance-quotes&amp;action=delete_quote&amp;yfq_id=<?php echo $stock->yfq_id; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this quote?')"><?php echo __('Delete'); ?></a></td>
				</tr>

			<?php

				if ($alt = 'alternate') { $alt = ''; } elseif ($alt = '') { $alt = 'alternate'; }

			}
			?>

			</tbody>
		</table>

	</div>

<?php
}

// link to css stylesheet
add_action('wp_head', 'addHeaderCode');

function  addHeaderCode() {
            echo "\n" . '<style type="text/css" media="screen"><!-- @import url( ' . get_bloginfo('wpurl') . '/wp-content/plugins/yahoo-finance-quotes/styles.css' . ' ); --></style>' . "\n";

}

// detect special tags in posted content
add_filter( 'the_content', 'modifyContent' );

function  modifyContent($content) {

	global $wpdb;
    $table_name = $wpdb->prefix . YFQ_TABLE;

	$pos = 0;
	
	while(true){
		$pos = strpos($content, '[yfq#', $pos);
        
		if($pos){

			$closed_pos = strpos($content, ']', $pos);

			if($closed_pos){
				$category = substr($content, ($pos + 5), ($closed_pos - ($pos + 5)));
							
				$yfq_tag = substr($content, $pos, ($closed_pos - $pos + 1));


				$results = $wpdb->get_results("SELECT yfq_symbol, yfq_category FROM " . $table_name . " WHERE yfq_category = '" . $category . "' ORDER BY yfq_id ASC;" );
				
				$symbols = '';

				foreach ( $results as $result ) {

					$symbols .= $result->yfq_symbol . '+';
				}


				$symbols = trim($symbols, "+");

				$yahoo = new yahoo;
				$yahoo->get_stock_quotes($symbols);

				$yfq_html = construct_yfq_html($yahoo, $category);
				
                $content = str_replace($yfq_tag, $yfq_html, $content);
			}
		} else 
			break;
	}

	return ($content);
}

// php function to display quotes in wordpress
function yfq_output($category='') {

	global $wpdb;

	$table_name = $wpdb->prefix . YFQ_TABLE;

	$results = $wpdb->get_results("SELECT yfq_symbol, yfq_category FROM " . $table_name . " WHERE yfq_category = '" . $category . "' ORDER BY yfq_id ASC;" );
    
	$symbols = '';

	foreach ( $results as $result ) {

		$symbols .= $result->yfq_symbol . '+';
	}


	$symbols = trim($symbols, "+");

    $yahoo = new yahoo;
	$yahoo->get_stock_quotes($symbols);

    echo construct_yfq_html($yahoo, $category);
}

?>

<?php
    function construct_yfq_html($yahoo, $caption){

		global $wpdb;

		$table_name = $wpdb->prefix . YFQ_TABLE;
		$powered_by_link = $wpdb->get_var($wpdb->prepare("SELECT yfq_symbol FROM " . $table_name . " WHERE yfq_category='enable_powered_by';"));

		$yfq_html = '<table class="yfq" summary="Quotes from Yahoo Finance">';
		$yfq_html .= '<caption>' . $caption . '</caption>';
		$yfq_html .=	'<thead>';
		$yfq_html .=	'<tr class="odd">';
		$yfq_html .=		'<th scope="col" abbr="Quote">Quote</td>';
		$yfq_html .= 		'<th scope="col" abbr="Last">Last</th>';
		$yfq_html .=		'<th scope="col" abbr="Change">Change</th>';
		$yfq_html .=	'</tr>';	
		$yfq_html .=	'</thead>';

		for ($x = 0; $x < $yahoo->size; $x++) {

			if($x % 2)
				$yfq_html .=	'<tr class="odd">';
			else
				$yfq_html .=	'<tr>';

			$yfq_html .=		'<th scope="row" class="column1"><a href="#" class="quote" title="' . $yahoo->quotes[$x][1] . '">' . $yahoo->quotes[$x][0] . '</th>';
			$yfq_html .=		'<td>'. $yahoo->quotes[$x][2] . '</td>';

			if (strlen(strstr($yahoo->quotes[$x][3],'+')) > 0)
				$yfq_html .=		'<td class="up">'. $yahoo->quotes[$x][3] . '</td>';
			else if (strlen(strstr($yahoo->quotes[$x][3],'-')) > 0)
				$yfq_html .=		'<td class="down">'. $yahoo->quotes[$x][3] . '</td>';
			else
				$yfq_html .=		'<td class="nc">'. $yahoo->quotes[$x][3] . '</td>';
			$yfq_html .=	'</tr>';

		}
        
		if($powered_by_link == 'yes') {
			$yfq_html .=    '<tfoot>';
			$yfq_html .= 	'<tr class="odd">';

			$yfq_html .=	'<th colspan="3"><em>Powered by</em> <a href="http://www.metastocktradingsystem.com" target="_blank">MetaStock Trading System</a></th>';

			$yfq_html .= 	'</tr>';
			$yfq_html .=	'</tfoot>';
		}
		$yfq_html .=	'</table>';

		return ($yfq_html);
	}

	Class yahoo
	{
		var $quotes;
		var $size = 0;

		/* Function. */
		function get_stock_quotes($symbols)
		{
				global $wpdb;
	
			$table_name = $wpdb->prefix . YFQ_TABLE;
			$yahoo_url = $wpdb->get_var($wpdb->prepare("SELECT yfq_symbol FROM " . $table_name . " WHERE yfq_category='yahoo_url';"));
			$yahoo_param = $wpdb->get_var($wpdb->prepare("SELECT yfq_symbol FROM " . $table_name . " WHERE yfq_category='yahoo_param';"));
			$url = $yahoo_url . '?s=' . $symbols . $yahoo_param;

			/**
			* Initialize the cURL session
			*/
			$ch = curl_init();

			/**
			* Set the URL of the page or file to download.
			*/
			curl_setopt($ch, CURLOPT_URL, $url);

			/**
			* Ask cURL to return the contents in a variable
			* instead of simply echoing them to the browser.
			*/
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);

			/**
			* Execute the cURL session
			*/
			$contents = curl_exec ($ch);

			/**
			* Close cURL session
			*/
			curl_close ($ch);

			$contents = str_replace('"', '', $contents);

			if (contents) {
				$line = explode("\n", $contents);

				foreach( $line as $value) {
					$data = explode(',', $value);
					
					$this->quotes[$this->size] = $data;
                    $this->size++; 

				}

				$this->size--;
				unset($this->quotes[$this->size]);
			}
		}
	}
?>

