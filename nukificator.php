<?php
echo "Access token needed (to perform the nuking). Please go to erwaysoftware.com/oauth to get an access token." . PHP_EOL . "Enter access token: ";

$handle = fopen ("php://stdin","r");
$line = fgets($handle);

$access_token = trim($line);

echo "validating token..." . PHP_EOL;

$accesstokenvalidateurl = 'https://api.stackexchange.com/2.2/access-tokens/' . $access_token;
$accesstokenvalidatedata = array();
$response = (new Curl)->exec($accesstokenvalidateurl . '?' . http_build_query($accesstokenvalidatedata), [CURLOPT_ENCODING => 'gzip']);

$tokenvalidate=json_decode($response);

$colors = new Colors();

if (count($tokenvalidate->{"items"}) > 0)
{
	echo $colors->getColoredString("Your token looks good. Wonderful.", "green") . PHP_EOL . PHP_EOL;
}
else
{
	echo $colors->getColoredString("Your token couldn't be validated. You might not be able to retag questions.", "red") . PHP_EOL . PHP_EOL;
}

echo 'Tag to inspect: ';

$handle = fopen ("php://stdin","r");
$line = fgets($handle);

$tag = trim($line);

echo "fetching last 100 [" . $tag  . "] questions...";

$questionsurl = 'https://api.stackexchange.com/2.2/questions';
$questionsdata = array('sort' => 'creation', "site" => 'stackoverflow', "tagged" => $tag, "key" => "6Z09liTt4uTQU*a4DYOXVQ((", "access_token" => $access_token, "filter" => "!Fcr3VId0gGli*1j_vQJZ0Ox6lU");
$response = (new Curl)->exec($questionsurl . '?' . http_build_query($questionsdata), [CURLOPT_ENCODING => 'gzip']);

echo " fetched" . PHP_EOL;

$obj=json_decode($response);

$questions = $obj->{"items"};


foreach ($questions as $question)
{
	echo PHP_EOL . "    " . $colors->getColoredString(htmlspecialchars_decode($question->{"title"}, ENT_QUOTES), "red") . PHP_EOL . PHP_EOL;

	echo $colors->getColoredString(mb_substr(htmlspecialchars_decode($question -> {"body_markdown"}, ENT_QUOTES), 0, 500), "blue") . PHP_EOL . PHP_EOL;

	foreach ($question->{"tags"} as $qtag) {
		echo $colors->getColoredString("[" . $qtag . "] ", "white", "red");
	}

	echo PHP_EOL . PHP_EOL;

	echo "rm [" . $tag . "] tag? (y/n): ";

	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);

	$response = trim($line);

	if ($response == "y")
	{
		echo "removing [" . $tag . "] tag...";
		
		$taglist = implode(";", $question->{"tags"});
		$taglist = str_replace($tag, "", $taglist);
		$taglist = str_replace(";;", ";", $taglist);

		$editURL = 'https://api.stackexchange.com/2.2/questions/' . $question->{"question_id"} . '/edit';
		$editData = array('site' => 'stackoverflow', 'preview' => 'false', 'id' => $question->{"question_id"}, 'key' => "6Z09liTt4uTQU*a4DYOXVQ((", 'access_token' => $access_token, 'title' => html_entity_decode($question -> {"title"}, ENT_QUOTES), 'body' => html_entity_decode($question -> {"body_markdown"}, ENT_QUOTES), 'tags' => $taglist, 'comment' => 'rm [' . $tag . '] tag');
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded, Accept-Encoding: gzip;q=0, compress;q=0\r\n",
				'method'  => 'POST',
				'content' => http_build_query($editData),
				'ignore_errors' => true,
			),
		);
		$context = stream_context_create($options);
		$obj = json_decode(gzdecode(file_get_contents($editURL, false, $context)));
		
	}
}

class Curl
{
  protected $info = [];
  
  public function exec($url, $setopt = array(), $post = array())
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:15.0) Gecko/20100101 Firefox/15.0.1');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    if ( ! empty($post))
    {
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    }
    if ( ! empty($setopt))
    {
      foreach ($setopt as $key => $value)
      {
        curl_setopt($curl, $key, $value);
      }
    }
    $data = curl_exec($curl);
    $this->info = curl_getinfo($curl);
    curl_close($curl);
    return $data;
  }
 
  public function getInfo()
  {
    return $this->info;
  }
}
class Colors {

	private $foreground_colors = array();
	private $background_colors = array();

	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}

		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";

		return $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors() {
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors() {
		return array_keys($this->background_colors);
	}
}
