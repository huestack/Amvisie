<?php


namespace Amvisie\Core\Api;

/**
 * Parses content and files from php://input stream, specially on PUT request when user data is not available in $_POST global.
 * @author Ritesh Gite <huestack@yahoo.com>
 */

class HttpInput
{
    /**
     * A request object.
     * @var \Amvisie\Core\HttpRequest
     */
    private $request;
    
    /**
     * An array of data parsed from php://input
     * @var array 
     */
    private $data = array();
    
    /**
     * An array of files.
     * @var array 
     */
    private $files = array();
    
    /**
     * Initializes an instance of HttpInput class.
     * @param \Amvisie\Core\HttpRequest $request
     */
    public function __construct(\Amvisie\Core\HttpRequest $request)
    {
        $this->request = $request;
        
        $this->parse();
    }
    
    public function &getData() : array
    {
        return $this->data;
    }
    
    public function &getFiles() : array
    {
        return $this->files;
    }
    
    private function parse() : void
    {
        if ($this->request->isBodyUrlEncoded()) {
            parse_str(file_get_contents('php://input'), $this->data);
        } else if ($this->request->isBodyFormData()) {
            $this->parseFormData();
        } else {
            $this->data['content'] = file_get_contents('php://input');
        }
    }
    
    private function parseFormData() 
    {
        $matches = array();
        
        // read incoming data
        $input = file_get_contents('php://input');

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', filter_input(INPUT_SERVER, 'CONTENT_TYPE'), $matches);

        // content type is probably regular form-encoded
        if (!count($matches)) {
            // we expect regular puts to containt a query string containing data
            parse_str(urldecode($input), $this->data);
            return;
        }

        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block) {
            if (empty($block)) {
                continue;
            }

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== false) {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                $this->files['files'][$matches[1]] = $matches[2];
            } else { // parse all other fields
                
                if (strpos($block, 'filename') !== false) {
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                    preg_match('/Content-Type: (.*)?/', $matches[3], $mime);

                    // match the mime type supplied from the browser
                    $image = preg_replace('/Content-Type: (.*)[^\n\r]/', '', $matches[3]);

                    // get current system path and create tempory file name & path
                    $path = sys_get_temp_dir().'/php'.substr(sha1(rand()), 0, 6);

                    // write temporary file to emulate $_FILES super global
                    $err = file_put_contents($path, ltrim($image));
                    
                    $name = '';
                    
                    // Did the user use the infamous &lt;input name="array[]" for multiple file uploads?
                    if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
                        $name = $tmp[1];
                    } else {
                        $name = $matches[1];
                    }
                    
                    $this->files[$name]['name'][] = $matches[2];

                    // Create the remainder of the $_FILES super global
                    $this->files[$name]['type'][] = $mime[1];
                    $this->files[$name]['tmp_name'][] = $path;
                    $this->files[$name]['error'][] = $err;
                    $this->files[$name]['size'][] = filesize($path);
                } else {
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);

                    if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) {
                        $this->data[$tmp[1]][] = $matches[2];
                    } else {
                        $this->data[$matches[1]] = $matches[2];
                    }
                }
            }
        }
    }
}
