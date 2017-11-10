<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("admin/home", name="admin_home")
     */
    public function indexAction(Request $request)
    {
    	// replace this example code with whatever you need
        return $this->render('admin/adminHome.html.twig');
    }
}
