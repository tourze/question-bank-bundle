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
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\QuestionBankBundle\Entity\Tag;

#[AdminCrud(routePath: '/question-bank/tag', routeName: 'question_bank_tag')]
final class TagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('标签')
            ->setEntityLabelInPlural('标签管理')
            ->setPageTitle('index', '标签列表')
            ->setPageTitle('new', '新增标签')
            ->setPageTitle('edit', '编辑标签')
            ->setPageTitle('detail', '标签详情')
            ->setDefaultSort(['usageCount' => 'DESC', 'createTime' => 'DESC'])
            ->setSearchFields(['name', 'slug', 'description'])
            ->setPaginatorPageSize(30)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('查看');
            })
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('name', '标签名称')
                ->setColumns(6)
                ->setRequired(true)
                ->setHelp('标签的显示名称'),

            TextField::new('slug', '标签别名')
                ->setColumns(6)
                ->setRequired(true)
                ->setHelp('URL友好的唯一标识，支持字母、数字、短横线'),
        ];

        if (Crud::PAGE_INDEX === $pageName) {
            $fields[] = ColorField::new('color', '颜色')
                ->setColumns(2)
            ;
            $fields[] = IntegerField::new('usageCount', '使用次数')
                ->setColumns(2)
            ;
            $fields[] = BooleanField::new('valid', '有效')
                ->setColumns(1)
            ;
            $fields[] = TextareaField::new('description', '描述')
                ->setColumns(5)
                ->setMaxLength(100)
                ->renderAsHtml(false)
            ;
        } else {
            $fields[] = ColorField::new('color', '标签颜色')
                ->setColumns(6)
                ->setRequired(false)
                ->setHelp('选择标签的显示颜色（十六进制格式，如：#FF0000）')
            ;

            $fields[] = TextareaField::new('description', '标签描述')
                ->setColumns(12)
                ->setRequired(false)
                ->setHelp('对标签的详细说明')
                ->setNumOfRows(3)
            ;

            $fields[] = BooleanField::new('valid', '是否有效')
                ->setColumns(6)
                ->setHelp('无效的标签将不会在前台显示')
            ;
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            $fields[] = TextField::new('id', 'ID')
                ->setColumns(6)
            ;
            $fields[] = IntegerField::new('usageCount', '使用次数')
                ->setColumns(6)
            ;
            $fields[] = TextareaField::new('description', '详细描述')
                ->setColumns(12)
            ;
            $fields[] = AssociationField::new('questions', '关联问题')
                ->setColumns(12)
            ;
        }

        return $fields;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('valid')
            ->add('color')
        ;
    }
}
