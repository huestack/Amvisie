<?php
namespace Amvisie\Core\Web;

/**
 * Generates HTML response to send to client.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class ViewResponse extends BaseResponse
{
    /**
     * A reference to view that has to be executed.
     * @var View 
     */
    private $view;
    
    /**
     * Initiates a view reponse.
     * @param Amvisie\Core\Web\View $view A reference to instance of View class.
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Processes a view and writes content in output stream.
     */
    public function process() : void
    {
        $this->view->render();  // Generates content.
        $this->view->renderLayout();
    }
}