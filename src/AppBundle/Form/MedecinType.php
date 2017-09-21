<?php

namespace AppBundle\Form;

use AppBundle\Entity\Departement;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Region;
use AppBundle\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedecinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'region', EntityType::class,
                [
                    'class'       => Region::class,
                    'placeholder' => 'Sélectionner la région',
                    'mapped'      => false,
                    'required'    => false
                ]
            );

        $builder->get('region')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $region = $form->getData();

                $this->addDepartementField($form->getParent(), $region);
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                /** @var Medecin $data */
                $data = $form->getData();
                $ville = $data->getVille();

                if ($ville) {
                    $departement = $ville->getDepartement();
                    $region = $departement->getRegion();

                    $this->addDepartementField($form, $region);
                    $this->addVilleField($form, $departement);

                    $form->get('region')->setData($region);
                    $form->get('departement')->setData($departement);
                } else {
                    $this->addDepartementField($form, null);
                    $this->addVilleField($form, null);
                }
            }
        );
    }

    /**
     * Rajoute un champs departement au formulaire
     *
     * @param FormInterface $form
     * @param Region $region
     */
    private function addDepartementField(FormInterface $form, Region $region = null)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'departement', EntityType::class, null,
            [
                'class'           => Departement::class,
                'placeholder'     => $region ? 'Sélectionner le département' : 'Sélectionner la région',
                'mapped'          => false,
                'required'        => false,
                'auto_initialize' => false,
                'choices'         => $region ? $region->getDepartements() : []
            ]
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $departement = $form->getData();

                $this->addVilleField($form->getParent(), $departement);
            }
        );
        $form->add($builder->getForm());
    }

    /**
     * Ajoute un champs ville
     *
     * @param FormInterface $form
     * @param Departement $departement
     */
    private function addVilleField(FormInterface $form, Departement $departement = null)
    {
        $form->add(
            'ville', EntityType::class,
            [
                'class'       => Ville::class,
                'placeholder' => $departement ? 'Sélectionner la ville' : 'Sélectionnez le département',
                'choices'     => $departement ? $departement->getVilles() : []
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => Medecin::class
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'appbundle_medecin';
    }
}
