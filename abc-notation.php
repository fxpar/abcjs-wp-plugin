<?php
/*
Plugin Name: ABC Notation
Plugin URI: http://wordpress.paulrosen.net/plugins/abc-notation
Description: Include sheet music on your WordPress site by simply specifying the ABC style string in the shortcode <strong>[abcjs]</strong>. For a complete description of the syntax, see the <a href="http://wordpress.paulrosen.net/plugins/abc-notation">Plugin Site</a>.
Version: 6.1.3
Author: Paul Rosen
Author URI: http://paulrosen.net
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/*
Copyright (C) 2015-2022 Paul Rosen

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

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$originalABC ="";
$processedABC ="";
$uniqid;
$txtuniqid = uniqid();
//
//-- Allow upload of .abc files in "Add Media"
//
add_filter(
    'upload_mimes',
    function( $types ) {
        return array_merge( $types, array( 'abc' => 'text/plain' ) );
    }
);
//
//-- Add the javascript and css if there is a shortcode on the page.
//
function abcjs_conditionally_load_resources( $posts ) {

	if ( empty( $posts ) ) {
		return $posts;
	}
	$has_abcjs = false;
	foreach ( $posts as $post ) {
		if ( stripos( $post->post_content, '[abcjs' ) !== false ) {
			$has_abcjs = true;
			break;
		}
	}

	if ( $has_abcjs ) {
		
		//wp_enqueue_script( 'abcjs-plugin', plugins_url( '/abcjs-basic.js', __FILE__ ));
		wp_enqueue_script( 'abcjs-plugin', plugins_url( '/abcjs-basic-min.js', __FILE__ ));

		$plugin_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'style1', $plugin_url . 'abcjs-audio.css' );
	}

	return $posts;
}

add_filter( 'the_posts', 'abcjs_conditionally_load_resources' );
remove_filter('the_content', 'wptexturize');
// This turns the shortcode parameter back into the originally pasted string.

function process_abc( $content ) {
	global $originalABC ;
	global $processedABC;
	$originalABC = $content;
	$content2 = preg_replace("&<br />\r\n&", "\x01", $content);
	$content2 = preg_replace("&<br />\n&", "\x01", $content2);
	$content2 = preg_replace("&<br>\r\n&", "\x01", $content2);
	$content2 = preg_replace("&<br>\n&", "\x01", $content2);
	$content2 = preg_replace("&\r\n&", "\x01", $content2);
	$content2 = preg_replace("&\n&", "\x01", $content2);
	$content2 = preg_replace("-\"-", "\\\"", $content2);
	$content2 = preg_replace("-&#8221;-", "\\\"", $content2);
	$content2 = preg_replace("-&#8222;-", "\\\"", $content2);
	$content2 = preg_replace("-&#8217;-", "'", $content2);
	$content2 = preg_replace("-&#8243;-", "\\\"", $content2);
	$content2 = preg_replace("-&#8220;-", "\\\"", $content2);
	$content2 = preg_replace("-'-", "\\\'", $content2);
	$content2 = preg_replace("&<p>&", "\x01", $content2);
	$content2 = preg_replace("&</p>&", "\x01", $content2);
	//$content2 = preg_replace("-«-", "\\\"", $content2);
	//$content2 = preg_replace("-»-", "\\\"", $content2);
	
	return $content2;
}


// If a URL was passed in, then read the string from that, otherwise read the string from the contents.
function get_abc_string( $file, $content) {
	global $processedABC;
	if ($file) {
		$content2 = file_get_contents( $file );
		$content2 = preg_replace("&\r\n&", "\x01", $content2);
		$content2 = preg_replace("&\n&", "\x01", $content2);
		$content2 = preg_replace("-'-", "\\\'", $content2);
		$content2 = preg_replace("-\"-", "\\\"", $content2);
	} else
		$content2 = process_abc($content);
	//$processedABC = $content2;
	return $content2;
}

function construct_divs($number_of_tunes, $type, $class) {
	global $txtuniqid;
	global $originalABC ;
	$originalABCv2 = preg_replace("&<br />&", "", $originalABC);
	global  $processedABC;
	$output = "<div>";
	$ids = "";
	for ($i = 0; $i < $number_of_tunes; $i = $i + 1) {
		$id =  'abc-' . $type . '-' . uniqid();
		$output = $output . '<div id="' . $id . '" class="' . $class . ' abcjs-tune-number-' . $i .'"></div>' . "\n";
		$ids = $ids . "'" . $id . "',";
	}
	$output = $output . "<div id='abc-audio-" . $txtuniqid . "'></div><textarea id='abc-txt-" . $txtuniqid . "'>". $originalABCv2 ."</textarea></div>";
	return array( 'output' => $output, 'ids' => $ids );
}

function construct_divs2($number_of_tunes, $type, $class, $type2, $class2) {
	$output = "<div>";
	$ids = "";
	$ids2 = "";
	global $uniqid;
	for ($i = 0; $i < $number_of_tunes; $i = $i + 1) {
		$uniqid = uniqid();
		$id =  'abcjs-' . $type . '-' . $uniqid;
		$output = $output . '<div id="' . $id . '" class="' . $class . ' abcjs-tune-number-' . $i .'"></div>' . "\n ";
		$ids = $ids . "'" . $id . "',";
		$id =  'abcjs-' . $type2 . '-' . $uniqid;
		$output = $output . '<div id="' . $id . '" class="' . $class2 . ' abcjs-tune-number-' . $i .'"></div>' . "\n";
		$ids2 = $ids2 . "'" . $id . "',";
	}
    $output = $output . "</div>";
	return array( 'output' => $output, 'ids' => $ids, 'ids2' => $ids2 );
}

//
//-- Interpret the [abcjs] shortcode
//
function abcjs_create_music( $atts, $content ) {
	$a = shortcode_atts( array(
		'class' => 'abc-paper',
		'params' => '{}',
		'options' => '{}',
		'parser' => '{}',
		'engraver' => '{}',
		'render' => '{}',
		'file' => '',
		'number_of_tunes' => '1'
	), $atts );
		if ($a['options'] == '{}')
		$a['options'] = $a['params'];
	if ($a['options'] == '{}')
		$a['options'] = $a['parser'];
	$options = $a['options'];
	$options = preg_replace("-&#091;-", "[", $options);
	$options = preg_replace("-&#91;-", "[", $options);
	$options = preg_replace("-&#93;-", "]", $options);
	$options = preg_replace("-&#093;-", "]", $options);

	$content2 = get_abc_string($a['file'], $content);

	$ret = construct_divs($a['number_of_tunes'], 'paper', $a['class']);
	$output = $ret['output'];
	$ids = $ret['ids'];

	$output = $output . openJsSection() .
        'var abc = "' . $content2 . '".replace(/\x01/g,"\n");' . "\n" .
		'ABCJS.renderAbc([' . $ids . '], ' . "\n" .' abc, ' . "\n" . $options . ', ' . $a['engraver'] . ', ' . $a['render'] . ');' . "\n" .
        closeJsSection();

	return $output;
}
add_shortcode( 'abcjs', 'abcjs_create_music' );

//
//-- Interpret the [abcjs-midi] shortcode
// This creates only the audio control but no visual music.
//
function abcjs_create_midi( $atts, $content ) {
	
	$a = shortcode_atts( array(
		'class' => 'abc-midi',
        'params' => '{}',
        'options' => '{}',
		'parser' => '{}',
		'midi' => '{}',
		'file' => '',
		'number_of_tunes' => '1'
	), $atts );
    if ($a['parser'] == '{}')
        $a['parser'] = $a['params'];
    if ($a['parser'] == '{}')
        $a['parser'] = $a['options'];

	$content2 = get_abc_string($a['file'], $content);
	

	$ret = construct_divs($a['number_of_tunes'], 'midi', $a['class']);
	$output = $ret['output'];
	$ids = $ret['ids'];

        $output = $output . openJsSection() .
 		'var visualObjs = ABCJS.renderAbc("*", ' . "\n" .
 			'"' . $content2 . '".replace(/\x01/g,"\n"), ' . "\n" .
 			$a['parser'] .
 			', ' .
 			$a['midi'] .
 			');' . "\n" .
		    getSynthInit($ids, $a['midi'], 'null') . "\n" .
            closeJsSection();

	return $output;
}
add_shortcode( 'abcjs-midi', 'abcjs_create_midi' );

//
//-- Interpret the [abcjs-audio] shortcode
// This creates both the music and the audio control
//
function abcjs_create_audio( $atts, $content ) {
	$a = shortcode_atts( array(
		'class-paper' => 'abcjs-paper',
		'class-audio' => 'abcjs-audio',
		'params' => '{}',
        'options' => '{}',
		'file' => '',
		'number_of_tunes' => '1',
		'animate' => false,
		'qpm' => 'undefined'
	), $atts );
    if ($a['options'] == '{}')
        $a['options'] = $a['params'];
	
	$a['options'] = preg_replace("-&#091;-", "[", $a['options']);
	$a['options'] = preg_replace("-&#91;-", "[", $a['options']);
	$a['options'] = preg_replace("-&#93;-", "]", $a['options']);
	$a['options'] = preg_replace("-&#093;-", "]", $a['options']);
	$a['options'] = preg_replace("&oBracket&", "[", $a['options']);
	$a['options'] = preg_replace("&cBracket&", "]", $a['options']);

	$content2 = get_abc_string($a['file'], $content);

	$ret = construct_divs2($a['number_of_tunes'], 'paper', $a['class-paper'], 'midi', $a['class-audio']);
	$output = $ret['output'];
	$ids = $ret['ids'];
	$idsAudio = $ret['ids2'];
	$animateCallback = "";
	$animate = "null";
	if ($a['animate']) {
		$animateCallback = getCursorControl($ids);
		$animate = "cursorControl";
	}


	
	$output = $output . openJsSection() .
		$animateCallback .
		'var visualIds = [' . $ids . "];\n" .
		'var params = ' . $a['options'] . ";\n" .
        'var abc = "' . $content2 . '".replace(/\x01/g,"\n");' . "\n" .
		'var visualObjs = ABCJS.renderAbc(visualIds, abc, params);' . "\n" .
		'var midiIds = [' . $idsAudio . "];\n" .
        getSynthInit($idsAudio, $a['options'], $animate) . "\n" .
        closeJsSection();

	global $originalABC;
	global $uniqid;
	$output = $output . "<details style='padding:20px'>
    <summary><b>ABC code</b></summary>
	<div style='border:1px solid grey'>".$originalABC."</div>
</details>
<br/><button id='".  $uniqid .'-test\' onclick="var params = ' . $a['options'] . '; var abc = \'' . $content2 . '\'.replace(/\x01/g,\'\n\');  var newVisObj = ABCJS.renderAbc(\'abcjs-paper-' . $uniqid . '\',abc,{visualTranspose: 6, }, params); ">TestMidi2</button>';
//<br/><button id='".  $uniqid .'-test\' onclick="var params = ' . $a['options'] . '; var abc = \'' . $content2 . '\'.replace(/\x01/g,\'\n\'); var output = ABCJS.strTranspose(abc,\'abcjs-paper-' . $uniqid . '\', 2); var newVisObj = ABCJS.renderAbc(\'abcjs-paper-' . $uniqid . '\',output,{visualTranspose: 6, }, params); ABCJS.synth.createSynth(newVisObj[0]);">TestMidi2</button>'

	return $output;
}
add_shortcode( 'abcjs-audio', 'abcjs_create_audio' );

function openJsSection() {
    $section = <<<EOD
<script type="text/javascript">
(function () {
    
EOD;

    return $section;
}

function closeJsSection() {
	global  $txtuniqid;
    $section = <<<EOD
}());
</script>

EOD;

    return $section;
}

function getSynthInit($ids, $options, $cursorControl) {
    $synth = <<<EOD
		var synthControl = new ABCJS.synth.SynthController();
            var el = document.getElementById($ids null);
 		synthControl.load(el, $cursorControl, {displayLoop: true, displayRestart: true, displayPlay: true, displayProgress: true, displayWarp: true});
		synthControl.disable(true);
		var midiBuffer = new ABCJS.synth.CreateSynth();
		midiBuffer.init({
			visualObj: visualObjs[0],
 			options: $options
		}).then(function (response) {
			if (synthControl) {
				synthControl.setTune(visualObjs[0], false).then(function (response) {
				}).catch(function (error) {
					console.warn("Audio problem:", error);
				});
			}
		}).catch(function (error) {
			console.warn("Audio problem:", error);
		});
EOD;

    return $synth;
}

function getCursorControl($selector)
{
    $cursor_control = <<<EOD
		function CursorControl(selector) {
			var self = this;

			self.onStart = function() {
				var svg = document.querySelector("#" + selector + " svg");
				var cursor = document.createElementNS("http://www.w3.org/2000/svg", "line");
				cursor.setAttribute("class", "abcjs-cursor");
				cursor.setAttributeNS(null, 'x1', 0);
				cursor.setAttributeNS(null, 'y1', 0);
				cursor.setAttributeNS(null, 'x2', 0);
				cursor.setAttributeNS(null, 'y2', 0);
				svg.appendChild(cursor);

			};
			self.beatSubdivisions = 2;
			self.onEvent = function(ev) {
				if (ev.measureStart && ev.left === null)
					return; // this was the second part of a tie across a measure line. Just ignore it.

				var lastSelection = document.querySelectorAll("#" + selector + " svg .highlight");
				for (var k = 0; k < lastSelection.length; k++)
					lastSelection[k].classList.remove("highlight");

				for (var i = 0; i < ev.elements.length; i++ ) {
					var note = ev.elements[i];
					for (var j = 0; j < note.length; j++) {
						note[j].classList.add("highlight");
					}
				}

				var cursor = document.querySelector("#" + selector + " svg .abcjs-cursor");
				if (cursor) {
					cursor.setAttribute("x1", ev.left - 2);
					cursor.setAttribute("x2", ev.left - 2);
					cursor.setAttribute("y1", ev.top);
					cursor.setAttribute("y2", ev.top + ev.height);
				}
			};
			self.onFinished = function() {
				var els = document.querySelectorAll("#" + selector + " svg .highlight");
				for (var i = 0; i < els.length; i++ ) {
					els[i].classList.remove("highlight");
				}
				var cursor = document.querySelector("#" + selector + " svg .abcjs-cursor");
				if (cursor) {
					cursor.setAttribute("x1", 0);
					cursor.setAttribute("x2", 0);
					cursor.setAttribute("y1", 0);
					cursor.setAttribute("y2", 0);
				}
			};
		}

		var cursorControl = new CursorControl($selector null);

EOD;
    return $cursor_control;
}

function abcjs_editor( $atts, $content ) {
	global $txtuniqid;
	//$txtuniqid = uniqid();
    $a = shortcode_atts(array(
        'textarea' => 'abc-txt-' . $txtuniqid ,
        'class' => 'abc-paper',
		'file' => '',
        'options' => '{}',
		'params' => '{}',
		'animate' => true,
		'qpm' => 'undefined'
    ), $atts);

$a['options'] = preg_replace("-&#091;-", "[", $a['options']);
	$a['options'] = preg_replace("-&#91;-", "[", $a['options']);
	$a['options'] = preg_replace("-&#93;-", "]", $a['options']);
	$a['options'] = preg_replace("-&#093;-", "]", $a['options']);
	$a['options'] = preg_replace("&oBracket&", "[", $a['options']);
	$a['options'] = preg_replace("&cBracket&", "]", $a['options']);

$content2 = get_abc_string($a['file'], $content);
    $ret = construct_divs(1, 'editor', $a['class']);
	//$ret = construct_divs2($a['number_of_tunes'], 'paper', $a['class-paper'], 'midi', $a['class-audio']);
	//$idsAudio = $ret['ids2'];
    $ids = $ret['ids'];
    $divs = $ret['output'];
    $textarea = $a['textarea'];
    $options = $a['options'];
	
	if ($a['animate']) {
		$animateCallback = getCursorControl($ids);
		$animate = "cursorControl";
	}
	
	
	
    $editor = <<<EOD
	
 new ABCJS.Editor("$textarea", { canvas_id: $ids 
    abcjsParams: $options,
	synth: {
          el: "#abc-audio-$txtuniqid",
		  cursorControl,
          options: { displayRestart: true, displayPlay: true, displayProgress: true, options: {} }
        }
  });
EOD;

    $output = $divs . "\n" . openJsSection() . $animateCallback  . $editor . closeJsSection();
    return $output;
}

add_shortcode( 'abcjs-editor', 'abcjs_editor' );
