<?PHP
require("ask.php");

$ask = new askFm();

$ask->login('your nickname', 'your password');
$ask->ask('whoever', 'question text');

if(!$questions = $ask->fetchQuestions())
    echo $ask->lastError."\n";
else
    print_r($questions);

foreach ($questions as $key => $value) {
    echo $ask->checkQuestion($key) ? "Question $key exists\n" : "Question $key doesn't exits\n";
    if($value['text'] == 'do not answer this question')
    	$ask->delete($key);
    else
    	$ask->answer($key, 'your default answer');
}

echo $ask->checkQuestion('15670201') ? "Question 15670201 exists\n" : "Question 15670201 doesn't exits\n";

$ask->logout();
?>