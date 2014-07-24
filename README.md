Ask.fm API
==========

Introduction
------------

This is an unofficial PHP API for ask.fm.

Features
--------

- Asking questions, anonymously or with an account
- Fetching open questions for a profile
- Answering questions
- Deleting questions

Introduction
------------

The libraries are contained in `ask.php`. To use the API, simply write

    require('ask.php');
    
at the top of your file. Note that both `ask.php`and `simple_html_dom.php` are required.

`ask.php` defines the `class` `AskFm`. It exposes these properties:

    class askFm {
        public $lastError;
        
        public function __construct($cookieFile = "cookies.txt") {
        
        }
    
        public function ask($nickname, $question, $anon = false) {
        
        }
        
        public function login($nickname, $password){
        
        }
    
        public function logout() {
        
        }
    
        public function fetchQuestions() {
            
        }
    
        public function checkQuestion($questionId){
            
        }
    
        public function answer($questionId, $text){
            
        }
    
        public function delete($questionId){
            
        }
    
        public function deleteAll(){
            
        }
    }

The Ask object
--------------

To use the API you must declare a `new AskFm` object, like this:

    $ask = new AskFm;
    
lastError
---------

When using the API, you might run into errors, eg. when trying to fetch questions without having logged in first. In such cases, the function causing the error will return `false` and store the latest error in `$ask->lastError`.

Logging in
----------

In order to use some features, you need to log into your ask.fm account. Use the `login($username, $password)` function.

    $ask->login('nickname', 'password');

Asking questions
----------------

To ask questions, use `ask($nickname, $question, $anon = false)`. By default, questions will be asked using your account; if you wish to ask questions anonymously, pass `true` as the third parameter.

    $ask->ask('afilini', 'How are you?');
    $ask->ask('afilini', 'How are you?', true); // Same as above, but anonymously

Fetching questions
------------------

To fetch open (i.e. unanswered) questions for your account, use `fetchQuestions()`. The result is an array whose keys are question IDs, and whose values are question texts.
This function returns `false` if an error occurs; see the `lastError` paragraph for more informations on error handling.

    $questions = $ask->fetchQuestions();

Answering questions
-------------------

To answer an open question on your account, use `answer($questionId, $text)`.

    $ask->answer('123', 'I'm fine, thanks!');

Deleting questions
------------------

To delete a single question, use `delete($questionId)`; to delete all questions, use `deleteAll()`.

    $ask->delete('123');
    $ask->deleteAll();
    
Acknowledgements
------

This library uses simple_html_dom, by Jose Solorzano: http://sourceforge.net/projects/simplehtmldom/
This library was written by afilini.
This README was written by CapacitorSet.
