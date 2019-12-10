<?php

namespace OC\PlatformBundle\Form;

use OC\PlatformBundle\Form\ImageType;
use OC\PlatformBundle\Form\CategoryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use OC\PlatformBundle\Repository\CategoryRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Arbitrairemt, on récupère ts les catégorie qui commencent par "D"
        $pattern = 'G%';

        $builder
            ->add('date',      DateTimeType::class)
            ->add('title',     TextType::class)
            ->add('author',    TextType::class)
            ->add('content',   TextareaType::class)
            ->add('published', CheckboxType::class, ['required' => false])
            ->add('image',     ImageType::class)
            ->add('categories', EntityType::class, [
                'class' => 'OCPlatformBundle:Category',
                'choice_label'  => 'name',
                'multiple'      => true,
                'expanded'      => false,
                'query_builder' => function(CategoryRepository $repository) use($pattern) {
                    return $repository->getLikeQueryBuilder($pattern);
                  }
            ])
            ->add('save',      SubmitType::class);

            // ->add('updatedAt')
            // ->add('nbApplications')
            // ->add('slug')

            // ->add('categories', CollectionType::class, [
            //     'entry_type' => CategoryType::class,
            //     'allow_add' => true,
            //     'allow_delete' => true
            // ])  /* Pour le cas champs multiple */
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oc_platformbundle_advert';
    }


}
