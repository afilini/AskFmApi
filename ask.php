<?PHP
require("simple_html_dom.php");
require("config.php");

class askFm {
    public function __construct($nickname, $password, $cookieFile = "cookies.txt"){
        $this->_nickname = $nickname;
        $this->_password = $password;
        $this->_cookieFile = $cookieFile;
    }

    private function http($url, $urlRef = "http://ask.fm/", $post = false, $postData = array()){
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:30.0) Gecko/20100101 Firefox/30.0';
        $cookieFile = "cookies_tmp.txt";
        $ch = curl_init();
        $options = array(  
            CURLOPT_URL => $url,  
            CURLOPT_RETURNTRANSFER => 1,  
            CURLOPT_CONNECTTIMEOUT => 10 ,  
            CURLOPT_MAXREDIRS      => 10, 
            CURLOPT_REFERER => $urlRef,
            CURLOPT_FOLLOWLOCATION => TRUE,  
            CURLOPT_USERAGENT => $userAgent,  
            CURLOPT_HEADER => FALSE,  
            CURLOPT_COOKIEJAR => $this->_cookieFile,  
            CURLOPT_COOKIEFILE => $this->_cookieFile,  
            CURLOPT_SSL_VERIFYPEER => FALSE,  
            CURLOPT_SSL_VERIFYHOST => 2
        );  
        if($post){
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $postData;
        }
        curl_setopt_array($ch, $options);
        $pagina = curl_exec ($ch);
        curl_close ($ch); 
        return $pagina;
    }

    private function get_token(){
        $pagina = $this->http("http://ask.fm/");
        $pattern = '/(var AUTH_TOKEN = ")(.*)(";)/';
        preg_match($pattern, $pagina, $matches);
        return $matches[2];
    }

    public function ask($nickname, $question, $anon = false){
        $token = $this->get_token();

        $data = array(
            'authenticity_token' => $token,
            'question[question_text]' => $question
        );

        if($anon)
            $data['question[force_anonymous]'] = 1;

        $this->http("http://ask.fm/$nickname/questions/create/", "http://ask.fm/$nickname/", true, $data);
    }

    public function login(){
        $token = $this->get_token();

        $data = array(
            'authenticity_token' => $token,
            'login' => $this->_nickname,
            'password' => $this->_password
        );

        $this->http("http://ask.fm/session", "http://ask.fm/", true, $data);
    }

    public function logout(){
        $data = array('commit' => '');

        $this->http("http://ask.fm/logout", "http://ask.fm/account/wall", true, $data);
        unlink($this->_cookieFile);
    }

    public function fetchQuestions(){
        $questions = array();
        $pagina = $this->http("http://ask.fm/account/questions", "http://ask.fm/account/wall");
        $html = str_get_html($pagina);

        foreach ($html->find('div[class=questionBox]') as $value) {
            $id = str_replace("inbox_question_", "", $value->id);
            $text = $value->first_child()->first_child()->first_child()->innertext;
            $author = $value->first_child()->children(1);
            $authorNick = is_null($author) ? "ANONYMOUS" : str_replace("/", "", $author->first_child()->href);
            $author = is_null($author) ? "ANONYMOUS" : $author->first_child()->innertext;
            $questions[$id] = array(
                'text' => $text,
                'author_name' => $author,
                'author_nickname' => $authorNick
                );
        }

        return $questions;
    }
}

$ask = new askFm($nickname, $password);
$ask->login(); //Login is optional, you can always ask anonymous questions without logging in
print_r($ask->fetchQuestions());
?>