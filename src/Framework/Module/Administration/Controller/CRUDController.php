<?php

namespace Framework\Module\Administration\Controller;

use Doctrine\ORM\EntityManager;
use Framework\Module\Administration\Page;
use Framework\Controller\Controller;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Module\Router\Exception\AccessDeniedException;
use Framework\Module\Router\Exception\NotFoundException;

class CRUDController extends Controller
{
    /**
     * @param Request $request
     * @param         $pageName
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Exception
     */
    public function listAction(Request $request, $pageName)
    {
        if (!$this->isGranted($request)) {
            throw new AccessDeniedException();
        }

        /** @var Page $page */
        $page = $this->container->get('administration')->get($pageName);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();

        if (!$page) {
            throw new NotFoundException();
        }

        $entity = $page->getEntity();
        $metadata = $em->getMetadataFactory()->getMetadataFor($entity);

        $listFields = $page->getListFields();

        $formFields = [];
        foreach ($listFields as $field) {
            switch (true) {
                case ($metadata->getTypeOfField($field) !== null) :
                    $fieldOptions = [
                        'type' => $metadata->getTypeOfField($field)
                    ];
                    break;

                case ($metadata->getAssociationTargetClass($field) !== null) :
                    $fieldOptions = [
                        'type' => 'entity'
                    ];

                    break;

                default:
                    $fieldOptions = [];
            }

            $formFields[] = array_merge(['name' => $field], $fieldOptions);
        }

        $fields = $formFields;

        $items = $em->getRepository($entity)->findAll();

        return new Response($this->render('Framework/Module/Administration/View/list.html.twig', compact('fields', 'items', 'pageName')));
    }

    private function getEntityChoices($class)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();
        $metadata = $em->getMetadataFactory()->getMetadataFor($class);

        $entities = $em->getRepository($class)->findAll();

        $choices = [];
        foreach ($entities as $entity) {
            $name = method_exists($entity, '__toString') ? (string)$entity : '';
            $choices[array_shift($metadata->getIdentifierValues($entity))] = $name;
        }

        return $choices;
    }

    private function defineFieldType($metadata, $field, &$options)
    {
        switch (true) {
            case ($metadata->getTypeOfField($field) !== null) :
                $options['type'] = $metadata->getTypeOfField($field);
                break;

            case ($metadata->getAssociationTargetClass($field) !== null) :
                $class = $metadata->getAssociationTargetClass($field);

                $options['type'] = 'choice';
                $options['choices'] = $this->getEntityChoices($class);

                break;
        }
    }

    public function transformValue($type, $value)
    {
        switch ($type) {
            case 'boolean':
                return $value == 'on';
                break;
        }

        return $value;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function convertRequest(Request $request)
    {
        return \Symfony\Component\HttpFoundation\Request::create(
            $request->getUrl(),
            $request->getMethod(),
            $request->atributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            []
        );
    }

    /**
     * @param Request $request
     * @param         $pageName
     *
     * @return RedirectResponse|Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Exception
     */
    public function createAction(Request $request, $pageName)
    {
        /**
         * @var Page $page
         * @var EntityManager $em
         */
        if (!$this->isGranted($request)) {
            throw new AccessDeniedException();
        }
        $page = $this->container->get('administration')->get($pageName);
        $em = $this->container->get('doctrine')->getManager();
        if (!$page) {
            throw new NotFoundException();
        }
        $class = $page->getEntity();
        $formBuilder = $this->createFormBuilder(new $class());
        $page->buildEditForm($formBuilder);
        $form = $formBuilder->getForm();
        $form->handleRequest($this->convertRequest($request));
        if ($form->isValid()) {
            $object = $form->getData();
            $em->persist($object);
            $em->flush($object);
            $url = $this->container->get('router')->generateUrl('administration_list', [$pageName]);
            $response = new RedirectResponse($url);
        } else {
            $action = $this->container->get('router')->generateUrl('administration_create', [$pageName]);
            $form = $form->createView();
            $response = new Response($this->render('Framework/Module/Administration/View/create.html.twig', compact('form', 'action')));
        }

        return $response;
    }

    public function recognizeSetter($object, $name)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($object, $method)) {
            return $method;
        }

        throw new \Exception("Bad setter for {$name}.");
    }

    public function recognizeGetter($object, $name)
    {
        $variants = ['get', 'is'];
        foreach ($variants as $variant) {
            $method = $variant . ucfirst($name);
            if (method_exists($object, $method)) {
                return $method;
            }
        }

        throw new \Exception("Bad getter for {$name}.");
    }

    public function editAction(Request $request, $pageName, $id)
    {
        /**
         * @var Page          $page
         * @var EntityManager $em
         */
        $page = $this->container->get('administration')->get($pageName);
        $em = $this->container->get('doctrine')->getManager();

        if (!$page) {
            throw new NotFoundException();
        }

        $entityClass = $page->getEntity();
        $object = $em->getRepository($entityClass)->find($id);

        if (!$object) {
            throw new NotFoundException();
        }

        $builder = $this->createFormBuilder($object);
        $page->buildEditForm($builder);
        $form = $builder->getForm();
        $form->handleRequest($this->convertRequest($request));
        if ($form->isValid()) {
            $object = $form->getData();
            $em->persist($object);
            $em->flush($object);
            $url = $this->container->get('router')->generateUrl('administration_list', [$pageName]);
            $response = new RedirectResponse($url);
        } else {
            $action = $this->container->get('router')->generateUrl('administration_edit', [$pageName, $id]);
            $form = $form->createView();
            $response = new Response($this->render('Framework/Module/Administration/View/edit.html.twig', compact('form', 'action')));
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param         $name
     * @param         $id
     *
     * @return RedirectResponse
     * @throws NotFoundException
     * @throws \Exception
     */
    public function deleteAction(Request $request, $name, $id)
    {
        /**
         * @var Page          $page
         * @var EntityManager $em
         */
        $page = $this->container->get('administration')->get($name);
        $em = $this->container->get('doctrine')->getManager();

        if (!$page) {
            throw new NotFoundException();
        }

        $entityClass = $page->getEntity();
        $object = $em->getRepository($entityClass)->find($id);

        if (!$object) {
            throw new NotFoundException();
        }

        $em->remove($object);
        $em->flush();

        $url = $this->container->get('router')->generateUrl('administration_list', [$name]);

        return new RedirectResponse($url);
    }
}