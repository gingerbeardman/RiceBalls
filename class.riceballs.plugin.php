<?php if (!defined('APPLICATION')) exit();

$PluginInfo['RiceBalls'] = array(
	'Name' => 'Rice Balls',
	'Description' => 'Replaces <a href="http://en.wikipedia.org/wiki/Emoticon">emoticons</a> (smilies/smileys) with David Lanham\'s Rice Balls images from AdiumXtras.',
	'Version' 	=>	 '1.0.1',
	'MobileFriendly' => TRUE,
	'Author' 	=>	 "Matt Sephton",
	'AuthorEmail' => 'matt@gingerbeardman.com',
	'AuthorUrl' =>	 'http://www.gingerbeardman.com',
	'License' => 'GPL v2',
	'RequiredApplications' => array('Vanilla' => '>=2.0.18'),
);

/**
 * Note: Added jquery events required for proper display/hiding of emoticons
 * as write & preview buttons are clicked on forms in Vanilla 2.0.14. These
 * are necessary in order for this plugin to work properly.
 */

class RiceBallsPlugin implements Gdn_IPlugin {
	
	/**
	 * Replace emoticons in comments.
	 */
	public function Base_AfterCommentFormat_Handler($Sender) {
		if (!C('Plugins.RiceBalls.FormatEmoticons', TRUE))
			return;

		$Object = $Sender->EventArguments['Object'];
		$Object->FormatBody = $this->DoEmoticons($Object->FormatBody);
		$Sender->EventArguments['Object'] = $Object;
	}
	
	public function DiscussionController_Render_Before($Sender) {
		$this->_RiceBallsSetup($Sender);
	}

	/**
	 * Return an array of emoticons.
	 */
	public static function GetEmoticons() {
		return array(
			':-|' => 'Ambivalent',
			':|' => 'Ambivalent',
			'O:)' => 'Angel',
			'o:)' => 'Angel',
			'O:-)' => 'Angel',
			'O:)' => 'Angel',
			'o:-)' => 'Angel',
			'o:)' => 'Angel',
			'&gt;:o' => 'Angry',
			':&lt;' => 'Angry',
			':-&lt;' => 'Angry',
			':-#' => 'Blush',
			':#' => 'Blush',
			'8|' => 'BugEyed',
			'8-|' => 'BugEyed',
			':S' => 'Confused',
			':-S' => 'Confused',
			':s' => 'Confused',
			':-s' => 'Confused',
			'%)' => 'Crazy',
			'%-)' => 'Crazy',
			':’(' => 'Crying',
			':’-(' => 'Crying',
			':!' => 'FootInMouth',
			':-!' => 'FootInMouth',
			':(' => 'Frown',
			':-(' => 'Frown',
			':o' => 'Gasp',
			'=-o' => 'Gasp',
			'X)' => 'Grin',
			'&lt;3' => 'Heart',
			'8)' => 'Hot',
			'8-)' => 'Hot',
			':-*' => 'Kiss',
			':*' => 'Kiss',
			':O' => 'LargeGasp',
			'=O' => 'LargeGasp',
			'=-O' => 'LargeGasp',
			':-O' => 'LargeGasp',
			':-D' => 'Laugh',
			':D' => 'Laugh',
			'xD' => 'Laugh',
			'XD' => 'Laugh',
			':X' => 'LipsAreSealed',
			':-x' => 'LipsAreSealed',
			':-X' => 'LipsAreSealed',
			':x' => 'LipsAreSealed',
			':$' => 'MoneyMouth',
			'$)' => 'MoneyMouth',
			':-$' => 'MoneyMouth',
			'$-)' => 'MoneyMouth',
			'&gt;:-)' => 'Naughty',
			'&gt;:)' => 'Naughty',
			'&gt;:-&gt;' => 'Naughty',
			'&gt;:&gt;' => 'Naughty',
			':-B' => 'Nerd',
			':B' => 'Nerd',
			'8-B' => 'Nerd',
			'8B' => 'Nerd',
			'D:' => 'OhNoes',
			'P-[' => 'Pirate',
			'P-|' => 'Pirate',
			':pirate' => 'Pirate',
			':ar' => 'Pirate',
			':yarr' => 'Pirate',
			'(sarc)' => 'Sarcastic',
			'&lt;/sarcasm&gt;' => 'Sarcastic',
			'^)' => 'Sarcastic',
			':[[' => 'Sick',
			':-)' => 'Smile',
			':)' => 'Smile',
			'^_^' => 'Smile',
			':P' => 'StickingOutTongue',
			':-p' => 'StickingOutTongue',
			':-P' => 'StickingOutTongue',
			':p' => 'StickingOutTongue',
			'(N)' => 'ThumbsDown',
			'(n)' => 'ThumbsDown',
			'(Y)' => 'ThumbsUp',
			'(y)' => 'ThumbsUp',
			':\\' => 'Undecided',
			':/' => 'Undecided',
			':-\\' => 'Undecided',
			':-/' => 'Undecided',
			'&gt;:-O' => 'VeryAngry',
			':@' => 'VeryAngry',
			':-@' => 'VeryAngry',
			';-)' => 'Wink',
			';)' => 'Wink',
			':-d' => 'Yum',
			':d' => 'Yum',
			':agbic' => 'AGBIC',
			':nes' => 'AGBIC',
			':love' => 'Love',
			'(L)' => 'Love',
			':trophy' => 'Trophy',
			':prize' => 'Trophy',
			':winner' => 'Trophy'
		);
	}
	
	/**
	 * Replace emoticons in comment preview.
	 */
	public function PostController_AfterCommentPreviewFormat_Handler($Sender) {
		if (!C('Plugins.RiceBalls.FormatEmoticons', TRUE))
			return;
		
		$Sender->Comment->Body = $this->DoEmoticons($Sender->Comment->Body);
	}
	
	public function PostController_Render_Before($Sender) {
		$this->_RiceBallsSetup($Sender);
	}
	
	/**
	 * Thanks to punbb 1.3.5 (GPL License) for this function - ported from their do_smilies function.
	 */
	public static function DoEmoticons($Text) {
		$Text = ' '.$Text.' ';

		// special case crying smiley to work around SmartyPants
		$Text = str_replace(array(':&#8217;(', ':&#8217;-('), array(':’(', ':’-('), $Text);

		$Emoticons = RiceBallsPlugin::GetEmoticons();
		foreach ($Emoticons as $Key => $Replacement) {
			if (strpos($Text, $Key) !== FALSE)
				$Text = preg_replace(
					"#(?<=[>\s])".preg_quote($Key, '#')."(?=\W)#m",
					'<span class="RiceBall RiceBall' . $Replacement . '" title="' . $Replacement . '"><span>' . $Key . '</span></span>',
					$Text
				);
		}

		return substr($Text, 1, -1);
	}

	/**
	 * Prepare a page to be emotified.
	 */
	private function _RiceBallsSetup($Sender) {
		$Sender->AddJsFile('riceballs.js', 'plugins/RiceBalls');   
		$Sender->AddCssFile('riceballs.css', 'plugins/RiceBalls');
		// Deliver the emoticons to the page.
		$Sender->AddDefinition('RiceBalls', base64_encode(json_encode($this->GetEmoticons())));
	}
	
	public function Setup() {
		//SaveToConfig('Plugins.RiceBalls.FormatEmoticons', TRUE);
		SaveToConfig('Garden.Format.Hashtags', FALSE); // Autohashing to search is incompatible with RiceBalls
	}
	
}
?>