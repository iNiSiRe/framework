<?php

namespace Framework\Module\Administration\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Framework\Module\Administration\Page;
use Framework\Controller\Controller;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Module\Doctrine\Doctrine;
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
     * @param         $pageName
     *
     * @return RedirectResponse|Response
     * @throws AccessDeniedException
     * @throws NotFoundException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Exception
     */
    public function createAction(Request $request, $pageName)
    {
        if (!$this->isGranted($request)) {
            throw new AccessDeniedException();
        }

        /** @var Page $page */
        /** @var EntityManager $em */
        $page = $this->container->get('administration')->get($pageName);
        $em = $this->container->get('doctrine')->getManager();

        if (!$page) {
            throw new NotFoundException();
        }

        $entity = $page->getEntity();
        $metadata = $em->getMetadataFactory()->getMetadataFor($entity);

        $editFields = $page->getEditFields();

        $formFields = [];
        foreach ($editFields as $field => $options) {

            if (!isset($options['type'])) {
                $this->defineFieldType($metadata, $field, $options);
            }

            $formFields[] = array_merge(['name' => $field], $options);
        }

        $fields = $formFields;

        if ($request->isMethod('post')) {
            $item = new $entity;
            foreach ($fields as $field) {

                $name = $field['name'];

                if ($field['type'] == 'file') {
                    $value = $request->files->get($name);
                } else {
                    $value = $this->transformValue($field['type'], $request->atributes->get($name));
                }

                switch (true) {
                    case ($metadata->getTypeOfField($name) !== null) :
                        $setter = 'set' . ucfirst($name);
                        if (method_exists($item, $setter)) {
                            $item->$setter($value);
                        }
                        break;

                    case ($metadata->getAssociationTargetClass($name) !== null) :
                        $association = $metadata->getAssociationMapping($name);
                        $targetClass = $association['targetEntity'];
                        if (!empty($value)) {
                            switch ($association['type']) {
                                case ClassMetadataInfo::MANY_TO_MANY:
                                    $entityValue = [];
                                    if (is_array($value)) {
                                        foreach ($value as $id) {
                                            $entityValue[] = $em->getRepository($targetClass)->find($id);
                                        }
                                    } else {
                                        $entityValue[] = $em->getRepository($targetClass)->find($value);
                                    }
                                    $value = $entityValue;
                                    break;

                                case ClassMetadataInfo::MANY_TO_ONE:
                                    $value = $em->getRepository($targetClass)->find($value);
                                    break;
                            }
                        }
                        $setter = $this->recognizeSetter($item, $name);
                        $item->$setter($value);
                        break;

                    default:
                }
            }
            $em->persist($item);
            $em->flush();

            $url = $this->container->get('router')->generateUrl('administration_list', [$pageName]);

            return new RedirectResponse($url);
        }

        $action = $this->container->get('router')->generateUrl('administration_create', [$pageName]);

        return new Response($this->render('Framework/Module/Administration/View/create.html.twig', compact('fields', 'action')));
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

        $fields = $page->getEditFields();
        $metadata = $em->getMetadataFactory()->getMetadataFor($entityClass);

        $form = [];
        foreach ($fields as $field => $options) {
            if (!isset($options['type'])) {
                $this->defineFieldType($metadata, $field, $fields[$field]);
            }

            $getter = $this->recognizeGetter($object, $field);
            $value = $object->$getter();

            switch (true) {
                case ($metadata->getTypeOfField($field) !== null) :
                    $fields[$field]['value'] = $value;
                    break;

                case ($metadata->getAssociationTargetClass($field) !== null) :
                    $association = $metadata->getAssociationMapping($field);
                    $targetClass = $association['targetEntity'];
                    if (is_array($value) || $value instanceof \ArrayAccess) {
                        $fields[$field]['value'] = $value;
                    } else {
                        $fields[$field]['value'] = $em->getRepository($targetClass)->find($value);
                    }
                    break;

                default:
            }

            $form[] = array_merge(['name' => $field], $fields[$field]);
        }

        if ($request->isMethod('post')) {
            foreach ($fields as $field => $options) {
                if ($options['type'] == 'file') {
                    $value = $request->files->get($field);
                } else {
                    $value = $this->transformValue($options['type'], $request->atributes->get($field));
                }

                $setter = $this->recognizeSetter($object, $field);

                switch (true) {
                    case ($metadata->getTypeOfField($field) !== null) :
                        if ($value !== null) {
                            $object->$setter($value);
                        }
                        break;

                    case ($metadata->getAssociationTargetClass($field) !== null) :
                        $association = $metadata->getAssociationMapping($field);
                        $targetClass = $association['targetEntity'];
                        switch ($association['type']) {
                            case ClassMetadataInfo::MANY_TO_MANY:
                                $entityValue = [];
                                if (is_array($value)) {
                                    foreach ($value as $id) {
                                        $entityValue[] = $em->getRepository($targetClass)->find($id);
                                    }
                                } else {
                                    $entityValue[] = $em->getRepository($targetClass)->find($value);
                                }
                                $value = $entityValue;
                            break;

                            case ClassMetadataInfo::MANY_TO_ONE:
                                $value = $em->getRepository($targetClass)->find($value);
                                break;
                        }
                        $object->$setter($value);
                        break;
                }
            }

            $em->persist($object);
            $em->flush($object);

            $url = $this->container->get('router')->generateUrl('administration_list', [$pageName]);

            return new RedirectResponse($url);
        }

        $action = $this->container->get('router')->generateUrl('administration_edit', [$pageName, $id]);

        return new Response($this->render('Framework/Module/Administration/View/edit.html.twig', compact('form', 'action')));
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