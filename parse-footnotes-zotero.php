<?php
/*
Plugin Name: Convert Footnotes
Plugin URI: http://ben.balter.com/2011/03/20/regular-expression-to-parse-word-style-footnotes/
Description: Converts Word Footnotes to Simple Footnotes format. Requires Simple Footnotes installed, available at: http://wordpress.org/extend/plugins/simple-footnotes/. This version also replaces Zotero field codes with Zotpress shortcodes.
Version: 0.2
Author: Benjamin J. Balter
Author URI: http://ben.balter.com/
Revised by Marc Chehab: http://www.elcontexto.info/
*/

/**
* Function which uses regular expression to parse Microsoft Word footnotes
* into WordPress's Simple Footnotes format
*
* @link http://ben.balter.com/2011/03/20/regular-expression-to-parse-word-style-footnotes/
* @param string $content post content from filter hook
* @returns string post content with parsed footnotes
*/

function bb_parse_footnotes( $content ) {

global $post;
if ( !isset( $post ) )
return;

//if we have already parsed, kick
if ( get_post_meta($post->ID, 'parsed_footnotes') )
return $content;

$content = stripslashes( $content );
$content = str_replace("\xc2\xa0",' ',$content);

//build find and replace arrays

//grab all the Word-style footnotes into an array
$pattern = '/\<a( title\=\"\")? href\=\"[^\"]*\#_ftnref([0-9]+)\"\>\[([0-9]+)\]\<\/a\>(.*)/';
preg_match_all( $pattern, $content, $footnotes, PREG_SET_ORDER);
foreach ($footnotes as $footnote) {
$find[] = '/\<a( title\=\"\")? href\=\"[^\"]*\#_ftn'.$footnote[2].'\"\>(\<strong\>)?\['.$footnote[2].'\](\<\/strong\>)?\<\/a\>/';
$replace[] = '[ref]' . str_replace( array("\r\n", "\r", "\n"), "", $footnote[4]) . '[/ref]';
}

//grab all zotero fields into an array
$pattern2 = '/\{\s*ADDIN ZOTERO_ITEM.*http:\/\/zotero\.org\/.*items\/([^\"]*).*\}\s*\}/';
preg_match_all( $pattern2, $content, $zotfields, PREG_SET_ORDER);
foreach ($zotfields as $zotfield) {
	$find[] = '/\{\s*ADDIN ZOTERO_ITEM.*http:\/\/zotero\.org\/.*items\/'.$zotfield[1].'.*\}\s*\}/';
	$replace[] = '[zotpressInText item="{'.$zotfield[1].'}"]';
}

//remove all the original footnotes when done + last line + empty spaces at beginnings of references
$find[] = '/\<div\>\s*(\<p\>)?\<a( title\=\"\")? href\=\"[^\"]*\#_ftnref([0-9]+)\"\>\[([0-9]+)\]\<\/a\>(.*)\s*\<\/div\>\s+/s';
$replace[] = '';
$find[] = '/\<div\>(<\/div\>)?.{1,2}?(\<br clear\=\"all\" \/\>)?.{1,2}?\<hr align\=\"left\" size\=\"1\" width\=\"33\%\" \/\>/s';
$replace[] = '';
$find[] = '/\[ref\]\s*/';
$replace[] = '[ref]';

//PRINTSTUFF BY MARC
//print_r($zotfields);

//make the switch
$content = preg_replace( $find, $replace, $content );

//add meta so we know it has been parsed
//add_post_meta($post->ID, 'parsed_footnotes', true, true);

return addslashes($content);
}


add_filter( 'content_save_pre', 'bb_parse_footnotes' );
?>
