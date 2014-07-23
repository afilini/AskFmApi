<?PHP
require("simple_html_dom.php");

class askFm {
    public function __construct($cookieFile = "cookies.txt"){
        $this->_nickname = NULL;
        $this->_password = NULL;
        $this->_cookieFile = $cookieFile;
        $this->_loggedIn = false;
        $this->lastError = NULL;
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

        if($anon && $this->_loggedIn)
            $data['question[force_anonymous]'] = 1;

        $this->http("http://ask.fm/$nickname/questions/create/", "http://ask.fm/$nickname/", true, $data);
    }

    public function login($nickname, $password){
        $token = $this->get_token();

        $this->_nickname = $nickname;
        $this->_password = $password;

        $data = array(
            'authenticity_token' => $token,
            'login' => $this->_nickname,
            'password' => $this->_password
        );

        $return = $this->http("http://ask.fm/session", "http://ask.fm/", true, $data);
        $this->_loggedIn = strpos($return, '<div class="incorrectLogin"') === false;
        if(!$this->_loggedIn)
            $this->lastError = "Incorrect username or password";
        return $this->_loggedIn;
    }

    public function logout(){
        if($this->_loggedIn){
            $data = array('commit' => '');
            $this->http("http://ask.fm/logout", "http://ask.fm/account/wall", true, $data);
        }
        $this->_loggedIn = false;
        unlink($this->_cookieFile);
    }

    public function fetchQuestions(){
        if($this->_loggedIn){
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
        }else{
            $this->lastError = "Not logged in";
            return false;
        }
    }

    public function checkQuestion($questionId){
        if($this->_loggedIn){
            return array_key_exists($questionId, $this->fetchQuestions());
        }else{
            $this->lastError = "Not logged in";
            return false;
        }
    }

    public function answer($questionId, $text){
        if(!$this->_loggedIn){
            $this->lastError = "Not logged in";
            return false;
        }elseif(!$this->checkQuestion($questionId)){
            $this->lastError = "Question Doesn't Exists";
            return false;
        }else{
            $token = $this->get_token();

            $data = array(
                'question[answer_text]' => $text,
                'commit' => 'Answer',
                'question[submit_stream]' => 1,
                '_method' => 'put',
                'authenticity_token' => $token,
                'question[submit_twitter]' => 0,
                'question[submit_facebook]' => 0
                );

            $this->http("http://ask.fm/questions/$questionId/answer", "http://ask.fm/".$this->_nickname."/questions/$questionId/reply", true, $data);
            return true;
        }
    }

    public function delete($questionId){
        if(!$this->_loggedIn){
            $this->lastError = "Not logged in";
            return false;
        }elseif(!$this->checkQuestion($questionId)){
            $this->lastError = "Question Doesn't Exists";
            return false;
        }else{
            $token = $this->get_token();

            $data = array(
                '_method' => 'delete',
                'authenticity_token' => $token,
                );

            $this->http("http://ask.fm/questions/$questionId/delete", "http://ask.fm/".$this->_nickname."/questions/", true, $data);
            return true;
        }
    }

    public function deleteAll(){
        if(!$this->_loggedIn){
            $this->lastError = "Not logged in";
            return false;
        }else{
            $token = $this->get_token();

            $data = array(
                '_method' => 'delete',
                'authenticity_token' => $token,
                );

            $this->http("http://ask.fm/questions/delete", "http://ask.fm/".$this->_nickname."/questions/", true, $data);
            return true;
        }
    }
}
?>