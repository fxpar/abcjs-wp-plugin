<?php
/*
Plugin Name: ABC Notation
Plugin URI: http://wordpress.paulrosen.net/plugins/abc-notation
Description: Include sheet music on your WordPress site by simply specifying the ABC style string in the shortcode <strong>[abcjs]</strong>.
Version: 6.1.7
Author: Paul Rosen
Author URI: http://paulrosen.net
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Allow upload of .abc files
add_filter( 'upload_mimes', function( $types ) {
	return array_merge( $types, array( 'abc' => 'text/plain' ) );
});

// Load resources
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
		// Load from CDN
		wp_enqueue_script( 
			'abcjs-plugin', 
			'https://cdn.jsdelivr.net/npm/abcjs@6.6.3/dist/abcjs-basic-min.js', 
			array(), 
			'6.1.4', 
			false
		);
		
		$plugin_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'abcjs-style', $plugin_url . 'abcjs-audio.css', array(), '6.1.4' );
	}

	return $posts;
}
add_filter( 'the_posts', 'abcjs_conditionally_load_resources' );

// Load text domain
add_action( 'init', 'abcjs_load_textdomain' );
function abcjs_load_textdomain() {
	load_plugin_textdomain( 'abcjs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

//
//-- Preserve ABC content from WordPress modifications
//
function abcjs_preserve_abc_content($content) {
	remove_filter('the_content', 'wpautop');
	remove_filter('the_content', 'wptexturize');
	remove_filter('the_content', 'convert_chars');
	return $content;
}
add_filter('the_content', 'abcjs_preserve_abc_content', 1);

//
//-- Utility Functions
//
function abcjs_process_abc_content( $content ) {
	$content = strip_tags($content);
	$content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
	$content = str_replace("\r\n", "\n", $content);
	$content = str_replace("\r", "\n", $content);
	$content = preg_replace("/\n+/", "\n", $content);
	$content = trim($content);
	return $content;
}

function abcjs_get_abc_string( $file, $content ) {
	if ( ! empty( $file ) ) {
		$content2 = file_get_contents( $file );
		if ( $content2 === false ) {
			return '';
		}
		$content2 = str_replace("\r\n", "\n", $content2);
		$content2 = str_replace("\r", "\n", $content2);
		return trim($content2);
	}
	return abcjs_process_abc_content( $content );
}

function abcjs_clean_options( $options ) {
	$options = html_entity_decode($options, ENT_QUOTES, 'UTF-8');
	$options = preg_replace( "-&#091;-", "[", $options );
	$options = preg_replace( "-&#91;-", "[", $options );
	$options = preg_replace( "-&#93;-", "]", $options );
	$options = preg_replace( "-&#093;-", "]", $options );
	return $options;
}

function abcjs_create_more_controls( $uniqid, $abc_content = '' ) {
	$i18n_Apply = __( 'Apply', 'abcjs' );
	$i18n_ChordsOff = __( 'ChordsOff', 'abcjs' );
	$i18n_ChordsOff_title = __( 'ChordsOff title', 'abcjs' );
	$i18n_Apply_title = __( 'Apply title', 'abcjs' );
	
	$abc_content = htmlspecialchars($abc_content, ENT_QUOTES, 'UTF-8');
	
	$more_controls = <<<EOD
	<div id="abc-more-$uniqid" class="abc-more-controls" style="display:none; padding:15px 20px; background:#f8f9fa; border:1px solid #dee2e6; border-top:none; border-radius:0 0 4px 4px;">
		<div style="display:flex; flex-wrap:wrap; gap:15px; align-items:center; margin-bottom:10px;">
			<div>
				<label for="abc-sel-transpose-$uniqid" style="margin-right:5px; font-size:14px;">Transpose:</label>
				<select id="abc-sel-transpose-$uniqid" style="padding:3px 5px; border:1px solid #ced4da; border-radius:3px;">
					<option value="0">0</option>
					<option value="1">+1</option>
					<option value="2">+2</option>
					<option value="3">+3</option>
					<option value="4">+4</option>
					<option value="5">+5</option>
					<option value="6">+6</option>
					<option value="7">+7</option>
					<option value="8">+8</option>
					<option value="-1">-1</option>
					<option value="-2">-2</option>
					<option value="-3">-3</option>
					<option value="-4">-4</option>
					<option value="-5">-5</option>
					<option value="-6">-6</option>
					<option value="-7">-7</option>
					<option value="-8">-8</option>
				</select>
			</div>
			<div>
				<label for="swing-value-$uniqid" style="margin-right:5px; font-size:14px;">Swing:</label>
				<input id="swing-value-$uniqid" type="number" min="50" max="75" step="1" value="50" style="width:50px; padding:3px 5px; border:1px solid #ced4da; border-radius:3px;">
			</div>
			<div>
				<label title="$i18n_ChordsOff_title" style="font-size:14px;">
					<input type="checkbox" id="chordsoff-value-$uniqid" name="chordsoff-value-$uniqid" value="chordsoff-value-$uniqid" title="$i18n_ChordsOff_title" style="margin-right:5px;">
					$i18n_ChordsOff
				</label>
			</div>
			<div>
				<button id="abc-test-$uniqid" title="$i18n_Apply_title" style="padding:4px 15px; background:#0073aa; color:white; border:none; border-radius:3px; cursor:pointer; font-size:14px;">$i18n_Apply</button>
			</div>
		</div>
		<div>
			<textarea id='abc-txt-$uniqid' spellcheck='false' rows='6' style="width:100%; padding:8px; font-family:monospace; font-size:13px; border:1px solid #ced4da; border-radius:3px; resize:vertical;">$abc_content</textarea>
		</div>
	</div>
EOD;
	
	return $more_controls;
}

function abcjs_construct_divs( $number_of_tunes, $type, $class ) {
	$output = "<div>";
	$ids = array();
	
	for ( $i = 0; $i < $number_of_tunes; $i++ ) {
		$uniqid = uniqid();
		$id = 'abc-' . $type . '-' . $uniqid;
		$output .= '<div id="' . $id . '" class="' . $class . ' abcjs-tune-number-' . $i .'"></div>';
		$output .= "<div id='abc-audio-" . $uniqid . "'></div>";
		$ids[] = $id;
	}
	
	$output .= "</div>";
	return array( 
		'output' => $output, 
		'ids' => $ids, 
		'ids_json' => "['" . implode("','", $ids) . "']",
		'uniqid' => $uniqid 
	);
}

function abcjs_construct_divs_audio( $number_of_tunes, $class_paper, $class_audio ) {
	$output = "<div class='abcjs-container'>";
	$paper_ids = array();
	$audio_ids = array();
	$uniqid = '';
	
	for ( $i = 0; $i < $number_of_tunes; $i++ ) {
		$uniqid = uniqid();
		
		$paper_id = 'abcjs-paper-' . $uniqid;
		$paper_ids[] = $paper_id;
		
		// Partition (Style curseur pointeur pour indiquer qu'on peut cliquer)
		$output .= '<div id="' . $paper_id . '" class="' . $class_paper . ' abcjs-tune-number-' . $i . '" style="cursor: pointer;" title="Cliquez sur la partition pour lancer/suspendre la musique"></div>';
		
		$audio_id = 'abcjs-midi-' . $uniqid;
		$audio_ids[] = $audio_id;
		
		// Lecteur unique en bas
		$output .= '<div class="abcjs-audio-wrapper" style="display:flex; align-items:center; background:#f8f9fa; border:1px solid #dee2e6; border-top:none; border-radius:0 0 4px 4px; padding:4px 8px;">';
		$output .= '<div id="' . $audio_id . '" class="' . $class_audio . ' abcjs-tune-number-' . $i . '" style="flex:1;"></div>';
		$output .= '<button class="abcjs-more-toggle" data-target="abc-more-' . $uniqid . '" style="background:none; border:none; font-size:20px; padding:4px 12px; cursor:pointer; color:#6c757d; line-height:1;">⋮</button>';
		$output .= '</div>';
		
		$output .= abcjs_create_more_controls( $uniqid, '' );
	}
	
	$output .= "</div>";
	
	return array( 
		'output' => $output, 
		'paper_ids' => $paper_ids,
		'audio_ids' => $audio_ids,
		'paper_ids_json' => "['" . implode("','", $paper_ids) . "']",
		'audio_ids_json' => "['" . implode("','", $audio_ids) . "']",
		'uniqid' => $uniqid 
	);
}

function abcjs_open_js_section() {
	return '<script type="text/javascript">(function() {' . "\n";
}

function abcjs_close_js_section() {
	return '}());</script>' . "\n";
}

//
//-- Shortcode: [abcjs]
//
function abcjs_shortcode( $atts, $content ) {
	$a = shortcode_atts( array(
		'class' => 'abc-paper',
		'options' => '{}',
		'engraver' => '{}',
		'render' => '{}',
		'file' => '',
		'number_of_tunes' => '1'
	), $atts );
	
	$options = abcjs_clean_options( $a['options'] );
	$abc_string = abcjs_get_abc_string( $a['file'], $content );
	
	$ret = abcjs_construct_divs( $a['number_of_tunes'], 'paper', $a['class'] );
	
	$abc_string_escaped = esc_js( $abc_string );
	
	$output = $ret['output'];
	$output .= abcjs_open_js_section();
	$output .= 'console.log("ABC Content:", "' . $abc_string_escaped . '".replace(/\x01/g,"\\n"));' . "\n";
	$output .= 'var abc = "' . $abc_string_escaped . '".replace(/\x01/g,"\\n");' . "\n";
	$output .= 'ABCJS.renderAbc(' . $ret['ids_json'] . ', abc, ' . $options . ', ' . $a['engraver'] . ', ' . $a['render'] . ');' . "\n";
	$output .= abcjs_close_js_section();
	
	return $output;
}
add_shortcode( 'abcjs', 'abcjs_shortcode' );

//
//-- Shortcode: [abcjs-midi]
//
function abcjs_midi_shortcode( $atts, $content ) {
	$a = shortcode_atts( array(
		'class' => 'abc-midi',
		'options' => '{}',
		'file' => '',
		'number_of_tunes' => '1'
	), $atts );
	
	$options = abcjs_clean_options( $a['options'] );
	$abc_string = abcjs_get_abc_string( $a['file'], $content );
	
	$ret = abcjs_construct_divs( $a['number_of_tunes'], 'midi', $a['class'] );
	
	$abc_string_escaped = esc_js( $abc_string );
	
	$output = $ret['output'];
	$output .= abcjs_open_js_section();
	$output .= 'console.log("ABC Content (MIDI):", "' . $abc_string_escaped . '".replace(/\x01/g,"\\n"));' . "\n";
	$output .= 'var visualObjs = ABCJS.renderAbc("*", "' . $abc_string_escaped . '".replace(/\x01/g,"\\n"), ' . $options . ');' . "\n";
	$output .= 'var synthControl = new ABCJS.synth.SynthController();' . "\n";
	$output .= 'synthControl.load(document.getElementById(' . $ret['ids_json'] . '), null, {displayLoop: true, displayRestart: true, displayPlay: true, displayProgress: true, displayWarp: true});' . "\n";
	$output .= 'synthControl.disable(true);' . "\n";
	$output .= 'var midiBuffer = new ABCJS.synth.CreateSynth();' . "\n";
	$output .= 'midiBuffer.init({visualObj: visualObjs[0], options: ' . $options . '}).then(function(response) {';
	$output .= '  if (synthControl) {';
	$output .= '    synthControl.setTune(visualObjs[0], false, ' . $options . ');' . "\n";
	$output .= '  }';
	$output .= '}).catch(function(error) {' . "\n";
	$output .= '  console.warn("Audio problem:", error);' . "\n";
	$output .= '});' . "\n";
	$output .= abcjs_close_js_section();
	
	return $output;
}
add_shortcode( 'abcjs-midi', 'abcjs_midi_shortcode' );

//
//-- Shortcode: [abcjs-audio]
//
function abcjs_audio_shortcode( $atts, $content ) {
	$a = shortcode_atts( array(
		'class-paper' => 'abcjs-paper',
		'class-audio' => 'abcjs-audio',
		'options' => '{}',
		'file' => '',
		'number_of_tunes' => '1',
		'animate' => 'true'
	), $atts );
	
	$options = abcjs_clean_options( $a['options'] );
	$abc_string = abcjs_get_abc_string( $a['file'], $content );
	
	$ret = abcjs_construct_divs_audio( $a['number_of_tunes'], $a['class-paper'], $a['class-audio'] );
	
	$animate_callback = '';
	$animate_param = 'null';
	if ( filter_var( $a['animate'], FILTER_VALIDATE_BOOLEAN ) ) {
		$animate_callback = abcjs_get_cursor_control( $ret['paper_ids'][0] );
		$animate_param = 'cursorControl';
	}
	
	$abc_string_escaped = str_replace("\n", "\\n", $abc_string);
	$abc_string_escaped = str_replace('"', '\"', $abc_string_escaped);
	$abc_string_escaped = str_replace("'", "\'", $abc_string_escaped);
	
	$output = $ret['output'];
	
	// Script du bouton "Trois points"
	$output .= '<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		var toggles = document.querySelectorAll(".abcjs-more-toggle");
		toggles.forEach(function(btn) {
			btn.addEventListener("click", function(e) {
				e.stopPropagation();
				var target = document.getElementById(this.getAttribute("data-target"));
				if (target) {
					if (target.style.display === "none" || target.style.display === "") {
						target.style.display = "block";
						this.textContent = "✕";
						this.style.color = "#0073aa";
					} else {
						target.style.display = "none";
						this.textContent = "⋮";
						this.style.color = "#6c757d";
					}
				}
			});
		});
	});
	</script>';
	
	$output .= abcjs_open_js_section();
	
	if ( ! empty( $animate_callback ) ) {
		$output .= $animate_callback;
	}
	
	$output .= 'var paperIds = ' . $ret['paper_ids_json'] . ';' . "\n";
	$output .= 'var audioIds = ' . $ret['audio_ids_json'] . ';' . "\n";
	$output .= 'var options = ' . $options . ';' . "\n";
	$output .= 'var abc = "' . $abc_string_escaped . '";' . "\n";
	$output .= 'var visualObjs = ABCJS.renderAbc(paperIds, abc, options);' . "\n";
	
	$output .= 'if (visualObjs && visualObjs.length > 0) {' . "\n";
	$output .= '  var synthControl = new ABCJS.synth.SynthController();' . "\n";
	$output .= '  synthControl.load(document.getElementById(audioIds[0]), ' . $animate_param . ', {displayLoop: true, displayRestart: true, displayPlay: true, displayProgress: true, displayWarp: true});' . "\n";
	$output .= '  synthControl.disable(true);' . "\n";
	$output .= '  var midiBuffer = new ABCJS.synth.CreateSynth();' . "\n";
	$output .= '  midiBuffer.init({visualObj: visualObjs[0], options: options}).then(function(response) {' . "\n";
	$output .= '    if (synthControl) {' . "\n";
	$output .= '      synthControl.setTune(visualObjs[0], false, options);' . "\n";
	
	// LE DECLENCHEUR AU CLIC SUR LA PARTITION
	$output .= '      var paperEl = document.getElementById(paperIds[0]);' . "\n";
	$output .= '      var audioEl = document.getElementById(audioIds[0]);' . "\n";
	$output .= '      if (paperEl && audioEl) {' . "\n";
	$output .= '        paperEl.style.cursor = "pointer";' . "\n";
	$output .= '        paperEl.addEventListener("click", function() {' . "\n";
	$output .= '          var playButton = audioEl.querySelector(".abcjs-play");' . "\n";
	$output .= '          if (playButton) { playButton.click(); }' . "\n";
	$output .= '        });' . "\n";
	$output .= '      }' . "\n";
	
	$output .= '    }' . "\n";
	$output .= '  }).catch(function(error) {' . "\n";
	$output .= '    console.warn("Audio problem:", error);' . "\n";
	$output .= '  });' . "\n";
	$output .= '}' . "\n";
	
	$output .= abcjs_close_js_section();
	
	$output .= '<style>
		.abcjs-more-toggle { transition: all 0.2s ease; }
		.abcjs-more-toggle:hover { color: #0056b3 !important; }
		.abcjs-audio-wrapper .abcjs-inner-audio { width: 100% !important; }
		.abcjs-audio-wrapper .abcjs-controls { display: flex !important; align-items: center !important; width: 100% !important; }
		.abcjs-audio-wrapper .abcjs-buttons { display: flex !important; align-items: center !important; flex: 1 !important; }
		.abcjs-paper { transition: opacity 0.2s ease; }
		.abcjs-paper:hover { opacity: 0.85; }
	</style>';
	
	return $output;
}
add_shortcode( 'abcjs-audio', 'abcjs_audio_shortcode' );

//
//-- Helper: Cursor Control for animation
//
function abcjs_get_cursor_control( $selector ) {
	$selector = esc_js( $selector );
	
	return <<<EOD
	function CursorControl(selector) {
		var self = this;
		self.onStart = function() {
			var svg = document.querySelector("#" + selector + " svg");
			if (!svg) return;
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
			if (ev.measureStart && ev.left === null) return;
			var lastSelection = document.querySelectorAll("#" + selector + " svg .highlight");
			for (var k = 0; k < lastSelection.length; k++) {
				lastSelection[k].classList.remove("highlight");
			}
			for (var i = 0; i < ev.elements.length; i++) {
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
			for (var i = 0; i < els.length; i++) {
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
	var cursorControl = new CursorControl("$selector");
EOD;
}

function abcjs_editor_shortcode( $atts, $content ) {
	$a = shortcode_atts( array(
		'class' => 'abc-paper',
		'class-audio' => 'abcjs-audio',
		'options' => '{}',
		'file' => '',
		'animate' => 'true'
	), $atts );
	
	$options = abcjs_clean_options( $a['options'] );
	$abc_string = abcjs_get_abc_string( $a['file'], $content );
	
	$uniqid = uniqid();
	$paper_id = 'abc-editor-' . $uniqid;
	$audio_id = 'abc-audio-' . $uniqid;
	$textarea_id = 'abc-txt-' . $uniqid;
	
	$output = '<div class="abcjs-container">';
	$output .= '<div id="' . $paper_id . '" class="' . $a['class'] . '" style="cursor: pointer;" title="Cliquez ici pour Play / Pause"></div>';
	
	$output .= '<div class="abcjs-audio-wrapper" style="display:flex; align-items:center; background:#f8f9fa; border:1px solid #dee2e6; border-top:none; border-radius:0 0 4px 4px; padding:4px 8px;">';
	$output .= '<div id="' . $audio_id . '" class="' . $a['class-audio'] . '" style="flex:1;"></div>';
	$output .= '<button class="abcjs-more-toggle" data-target="abc-more-' . $uniqid . '" style="background:none; border:none; font-size:20px; padding:4px 12px; cursor:pointer; color:#6c757d; line-height:1;">⋮</button>';
	$output .= '</div>';
	
	$output .= abcjs_create_more_controls( $uniqid, $abc_string );
	$output .= '</div>';
	
	$output .= '<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		var toggles = document.querySelectorAll(".abcjs-more-toggle");
		toggles.forEach(function(btn) {
			if (!btn.getAttribute("data-has-listener")) {
				btn.setAttribute("data-has-listener", "true");
				btn.addEventListener("click", function(e) {
					e.stopPropagation();
					var target = document.getElementById(this.getAttribute("data-target"));
					if (target) {
						if (target.style.display === "none" || target.style.display === "") {
							target.style.display = "block";
							this.textContent = "✕";
							this.style.color = "#0073aa";
						} else {
							target.style.display = "none";
							this.textContent = "⋮";
							this.style.color = "#6c757d";
						}
					}
				});
			}
		});
	});
	</script>';
	
	$output .= '<style>
		.abcjs-more-toggle {
			transition: all 0.2s ease;
		}
		.abcjs-more-toggle:hover {
			color: #0056b3 !important;
		}
		.abcjs-audio-wrapper .abcjs-inner-audio {
			width: 100% !important;
		}
		.abcjs-audio-wrapper .abcjs-controls {
			display: flex !important;
			align-items: center !important;
			width: 100% !important;
		}
		.abcjs-audio-wrapper .abcjs-buttons {
			display: flex !important;
			align-items: center !important;
			flex: 1 !important;
		}
		/* Effet de survol sur la partition */
		.abc-paper {
			transition: opacity 0.2s ease;
		}
		.abc-paper:hover {
			opacity: 0.85;
		}
	</style>';
	
	$animate_callback = '';
	$animate_param = 'null';
	if ( filter_var( $a['animate'], FILTER_VALIDATE_BOOLEAN ) ) {
		$animate_callback = abcjs_get_cursor_control( $paper_id );
		$animate_param = 'cursorControl';
	}
	
	$abc_string_escaped = esc_js( $abc_string );
	
	$output .= abcjs_open_js_section();
	
	if ( ! empty( $animate_callback ) ) {
		$output .= $animate_callback;
	}
	
/// début coupure
	
	$output .= '
	console.log("ABC Content (Editor):", "' . $abc_string_escaped . '".replace(/\\x01/g,"\\\\n"));
	var editor;
	var editorOptions = ' . $options . ';
	
	// 1. On détermine si chordsOff est activé dans le shortcode (gère le booléen ou la chaîne "true")
	var isChordsOffDefault = editorOptions && (editorOptions.chordsOff === true || editorOptions.chordsOff === "true");
	
	var visualTransposeEl = document.querySelector("#abc-sel-transpose-' . $uniqid . '");
	var audioTransposeEl = document.querySelector("#abc-sel-transpose-' . $uniqid . '");
	var chordsOffCheckbox = document.querySelector("#chordsoff-value-' . $uniqid . '");
	var swingElement = document.querySelector("#swing-value-' . $uniqid . '");
	
	// 2. On synchronise la case à cocher HTML pour qu\'elle reflète le shortcode dès le départ
	if (chordsOffCheckbox) {
		chordsOffCheckbox.checked = isChordsOffDefault;
	}
	
	editor = new ABCJS.Editor("' . $textarea_id . '", {
	  canvas_id: "' . $paper_id . '",
	  abcjsParams: editorOptions,
	  synth: {
	    el: "#' . $audio_id . '",
	    cursorControl: ' . $animate_param . ',
	    options: { displayLoop: true, displayRestart: true, displayPlay: true, displayProgress: true, displayWarp: true },
	    synthParams: {
	      chordsOff: editorOptions.chordsOff === true || editorOptions.chordsOff === "true",
	      options: editorOptions
	    }
	  }
	});
	
	document.getElementById("abc-test-' . $uniqid . '").addEventListener("click", function() {
	  var renderParams = { selectTypes: false, responsive: "resize", visualTranspose: parseInt(visualTransposeEl.value, 10) };
	  editor.paramChanged(renderParams);
	  
	  // 4. Ici, chordsOffCheckbox.checked renverra bien "true" si elle a été initialisée cochée
	  var synthParams = Object.assign({}, editorOptions, {
	    midiTranspose: parseInt(audioTransposeEl.value, 10),
	    chordsOff: chordsOffCheckbox ? chordsOffCheckbox.checked : false,
	    swing: parseFloat(swingElement.value, 10)
	  });
	
	  
	 
	  
	  
	  editor.synthParamChanged(synthParams);
	});

 /// fin coupure


	// ÉCOUTEUR SUR LA PARTITION : Pilote directement linstance audio de léditeur
	setTimeout(function() {
		var paperEl = document.getElementById("' . $paper_id . '");
		if (paperEl && editor && editor.synth) {
			paperEl.addEventListener("click", function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				// On vérifie si le contrôleur audio est disponible
				var controller = editor.synth.synthControl;
				if (controller) {
					// Si la musique joue, on fait pause, sinon on lance la lecture
					if (controller.isPlaying) {
						controller.pause();
					} else {
						controller.play();
					}
				} else {
					console.warn("Le contrôleur audio dabcjs nest pas encore prêt.");
				}
			}, true);
		}
	}, 1000);
	';
	
	$output .= abcjs_close_js_section();
	
	return $output;
}
add_shortcode( 'abcjs-editor', 'abcjs_editor_shortcode' );