<?php

namespace Framework\Controller;

use Framework\DependencyInjection\Container\Service;
use Framework\Http\Request;
use Framework\Module\Doctrine\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
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
        $managerRegistry = new ManagerRegistry($this->container->get('doctrine')->getManager());

        return Forms::createFormFactoryBuilder()
            ->addExtension(new DoctrineOrmExtension($managerRegistry))
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();
    }

    /**
     * @param null $data
     *
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    public function createFormBuilder($data = null)
    {
        return $this->getFormFactory()->createBuilder('form', $data);
    }
}