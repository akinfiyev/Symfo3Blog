<?php
/**
 * Created by PhpStorm.
 * User: q
 * Date: 13.11.18
 * Time: 4:57
 */

namespace AppBundle\Form\Article;

use AppBundle\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'form-control',
                    'placeholder' => 'Title'],
                'label_attr' => ['class' => 'sr-only'],
            ])
            ->add('text', TextareaType::class, [
                'attr' => ['class' => 'form-control',
                    'placeholder' => 'Article text'],
                'label_attr' => ['class' => 'sr-only'],
            ])
            ->add('plainTags', TextType::class, [
                'attr' => ['class' => 'form-control',
                    'placeholder' => 'Tags via \', \' '],
                'label_attr' => ['class' => 'sr-only'],
            ])
            ->add('thumbnail', FileType::class, [
                'data_class' => null
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Article::class,
            'attr' => ['novalidate' => 'novalidate'],
        ));
    }
}
