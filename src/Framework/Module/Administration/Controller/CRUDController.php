<?php

namespace Framework\Module\Administration\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Framework\Module\Administration\Page;
use Framework\Controller\Controller;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Module\Router\Exception\NotFoundException;

class CRUDController extends Controller
{
    public function listAction(Request $request, $pageName)
    {
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
            $name = method_exists($entity, '__toString') ? (string) $entity : '';
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

    public function createAction(Request $request, $pageName)
    {
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

        if ($request->getMethod() == 'POST') {
            parse_str($request->getBody(), $data);
            $item = new $entity;
            foreach ($fields as $field) {

                $name = $field['name'];

                $value = $this->transformValue($field['type'], $data[$name]);

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
                        $inversedBy = $association['inversedBy'];
                        $entity = $em->getRepository($targetClass)->find($value);

                        $setter = 'set' . ucfirst($name);
                        if (method_exists($item, $setter)) {
                            $item->$setter($entity);
                        }

//                        $collectionGetter = 'get' . ucfirst($inversedBy);
//                        if (method_exists($entity, $collectionGetter)) {
//                            /** @var ArrayCollection $items */
//                            $items = $entity->$collectionGetter();
//                            $items->add($item);
//                        }
//
//                        $em->persist($entity);

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
}