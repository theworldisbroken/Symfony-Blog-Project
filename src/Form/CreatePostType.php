<?php

namespace App\Form;

use App\Entity\Blog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;


class CreatePostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'exampleFormControlTextarea1',
                ]
            ])
            ->add('body', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'exampleFormControlTextarea1',
                ]
            ])
            ->add('picture', FileType::class, [
                'required' => false,
                'attr' => [
                    'enctype' => 'multipart/form-data'
                ]
            ])
            ->add('Create', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary me-md-2 float-end',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
