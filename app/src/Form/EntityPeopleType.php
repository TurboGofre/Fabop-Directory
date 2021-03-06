<?php

namespace App\Form;

use App\Entity\EntityPeople;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Security\Core\Security;

class EntityPeopleType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null,array('label' => 'Nom',
                                    'attr' => array(
                                        'class' => 'cm-input'
                                    )))
            ->add('firstname', null,array('label' => 'Prénom',
                                          'attr' => array(
                                              'class' => 'cm-input'
                                          )))
            ->add('birthdate', BirthdayType::class,array('label' => 'Date de naissance',
                'attr' => array(
                    'class' => 'cm-input'
                )))
            ->add('newsletter', null,array('label' => 'Abonnement Newsletter',
                                            'attr' => array(
                                                'class' => 'cm-input'
                                            )))
            ->add('postal_code', null,array('label' => 'Code postal',
                                            'attr' => array(
                                                'class' => 'cm-input'
                                            )))
            ->add('city', null,array('label' => 'Ville',
                                    'attr' => array(
                                        'class' => 'cm-input'
                                    )))
            ->add('adresseMailing', null,array('label' => 'Mail',
                'attr' => array(
                    'class' => 'cm-input'
                )))
        ;
        if($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('institution', null,array('label' => 'Institution',
                'attr' => array(
                    'class' => 'cm-input'
                )));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EntityPeople::class,
        ]);
    }
}
