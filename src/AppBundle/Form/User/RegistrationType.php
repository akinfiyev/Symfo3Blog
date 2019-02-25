<?php


namespace AppBundle\Form\User;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => ['class' => 'form-control',
                    'placeholder' => 'Username'],
                'label_attr' => ['class' => 'sr-only'],
            ])
            ->add('email', TextType::class, [
                'attr' => ['class' => 'form-control',
                    'placeholder' => 'Email'],
                'label_attr' => ['class' => 'sr-only'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options' => ['label_attr' => ['class' => 'sr-only'],
                    'attr' => ['placeholder' => 'Password',
                        'class' => 'form-control']],
                'second_options' => ['label_attr' => ['class' => 'sr-only'],
                    'attr' => ['placeholder' => 'Repeat Password',
                        'class' => 'form-control']],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'attr' => ['novalidate' => 'novalidate'],
        ));
    }
}