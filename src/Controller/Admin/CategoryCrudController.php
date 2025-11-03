<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\QuestionBankBundle\Entity\Category;

/**
 * @extends AbstractCrudController<Category>
 */
#[AdminCrud(routePath: '/question-bank/category', routeName: 'question_bank_category')]
final class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('分类')
            ->setEntityLabelInPlural('分类管理')
            ->setPageTitle('index', '分类列表')
            ->setPageTitle('new', '新增分类')
            ->setPageTitle('edit', '编辑分类')
            ->setPageTitle('detail', '分类详情')
            ->setDefaultSort(['sortOrder' => 'ASC', 'createTime' => 'DESC'])
            ->setSearchFields(['name', 'code', 'description'])
            ->setPaginatorPageSize(30)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('code')
            ->add('parent')
            ->add('valid')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                AssociationField::new('parent', '上级分类')
                    ->setColumns(3),
                IntegerField::new('level', '层级')
                    ->setColumns(2),
                TextField::new('path', '完整路径')
                    ->setColumns(4),
                IntegerField::new('sortOrder', '排序')
                    ->setColumns(2),
                BooleanField::new('valid', '有效')
                    ->setColumns(1),
            ];
        }

        $fields = [
            TextField::new('name', '分类名称')
                ->setColumns(6)
                ->setRequired(true)
                ->setHelp('分类的显示名称'),

            TextField::new('code', '分类代码')
                ->setColumns(6)
                ->setRequired(true)
                ->setHelp('用于URL和内部引用的唯一标识'),
        ];

        if (Crud::PAGE_DETAIL === $pageName) {
            $fields[] = TextField::new('id', 'ID')
                ->setColumns(6)
            ;
            $fields[] = AssociationField::new('parent', '上级分类')
                ->setColumns(6)
            ;
            $fields[] = IntegerField::new('level', '层级')
                ->setColumns(6)
            ;
            $fields[] = TextField::new('path', '完整路径')
                ->setColumns(12)
            ;
            $fields[] = IntegerField::new('sortOrder', '排序顺序')
                ->setColumns(6)
            ;
            $fields[] = TextareaField::new('description', '分类描述')
                ->setColumns(12)
            ;
            $fields[] = BooleanField::new('valid', '是否有效')
                ->setColumns(6)
            ;
            $fields[] = AssociationField::new('children', '子分类')
                ->setColumns(12)
            ;
            $fields[] = AssociationField::new('questions', '包含问题')
                ->setColumns(12)
            ;
        } else {
            // For NEW and EDIT pages
            $fields[] = AssociationField::new('parent', '上级分类')
                ->setColumns(6)
                ->setRequired(false)
                ->setHelp('选择上级分类，留空则为根分类')
                ->autocomplete()
            ;

            $fields[] = IntegerField::new('sortOrder', '排序顺序')
                ->setColumns(6)
                ->setRequired(false)
                ->setHelp('数字越小排序越靠前，默认为0')
            ;

            $fields[] = TextareaField::new('description', '分类描述')
                ->setColumns(12)
                ->setRequired(false)
                ->setHelp('对分类的详细说明')
            ;

            $fields[] = BooleanField::new('valid', '是否有效')
                ->setColumns(6)
                ->setHelp('无效的分类将不会在前台显示')
            ;
        }

        return $fields;
    }
}
