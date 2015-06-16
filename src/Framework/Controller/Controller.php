<?php

namespace Framework\Controller;

use Framework\DependencyInjection\Container\Service;
use Framework\Http\Request;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * Class Controller
 *
 * @package Framework\Core
 */
class Controller extends Service
{
    /**
     * TODO: Temporary access restriction solution
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isGranted(Request $request)
    {
        return $request->cookies->get('token') === sha1('inisire:access');
    }

    /**
     * @param       $template
     * @param array $context
     *
     * @return mixed
     * @throws \Exception
     */
    public function render($template, $context = [])
    {
        return $this->container->get('twig')->render($template, $context);
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        $validator = Validation::createValidator();

        return Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();
    }
}