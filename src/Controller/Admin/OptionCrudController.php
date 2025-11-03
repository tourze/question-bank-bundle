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
use Tourze\QuestionBankBundle\Entity\Option;

/**
 * @extends AbstractCrudController<Option>
 */
#[AdminCrud(routePath: '/question-bank/option', routeName: 'question_bank_option')]
final class OptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Option::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('选项')
            ->setEntityLabelInPlural('选项管理')
            ->setPageTitle('index', '选项列表')
            ->setPageTitle('new', '新增选项')
            ->setPageTitle('edit', '编辑选项')
            ->setPageTitle('detail', '选项详情')
            ->setDefaultSort(['question' => 'ASC', 'sortOrder' => 'ASC'])
            ->setSearchFields(['content', 'explanation'])
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
            ->add('question')
            ->add('content')
            ->add('isCorrect')
            ->add('valid')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                TextField::new('content', '选项内容')
                    ->setColumns(4)
                    ->setMaxLength(50),
                BooleanField::new('isCorrect', '正确答案')
                    ->setColumns(1),
                IntegerField::new('sortOrder', '排序')
                    ->setColumns(1),
                BooleanField::new('valid', '有效')
                    ->setColumns(1),
                TextareaField::new('explanation', '解释')
                    ->setColumns(1)
                    ->setMaxLength(30)
                    ->renderAsHtml(false),
            ];
        }

        $fields = [
            AssociationField::new('question', '所属问题')
                ->setColumns(4)
                ->setRequired(true)
                ->setHelp('选择该选项属于哪个问题')
                ->autocomplete(),
        ];

        if (Crud::PAGE_DETAIL === $pageName) {
            $fields[] = TextField::new('id', 'ID')
                ->setColumns(6)
            ;
            $fields[] = TextareaField::new('content', '选项内容')
                ->setColumns(12)
            ;
            $fields[] = BooleanField::new('isCorrect', '是否为正确答案')
                ->setColumns(6)
            ;
            $fields[] = IntegerField::new('sortOrder', '排序顺序')
                ->setColumns(6)
            ;
            $fields[] = TextareaField::new('explanation', '选项解释')
                ->setColumns(12)
            ;
            $fields[] = BooleanField::new('valid', '是否有效')
                ->setColumns(6)
            ;
        } else {
            // For NEW and EDIT pages
            $fields[] = TextareaField::new('content', '选项内容')
                ->setColumns(12)
                ->setRequired(true)
                ->setHelp('选项的具体内容')
                ->setNumOfRows(3)
            ;

            $fields[] = BooleanField::new('isCorrect', '是否为正确答案')
                ->setColumns(6)
                ->setHelp('标记该选项是否为正确答案')
            ;

            $fields[] = IntegerField::new('sortOrder', '排序顺序')
                ->setColumns(6)
                ->setRequired(false)
                ->setHelp('数字越小排序越靠前，默认为0')
            ;

            $fields[] = TextareaField::new('explanation', '选项解释')
                ->setColumns(12)
                ->setRequired(false)
                ->setHelp('对该选项的详细解释说明')
                ->setNumOfRows(3)
            ;

            $fields[] = BooleanField::new('valid', '是否有效')
                ->setColumns(6)
                ->setHelp('无效的选项将不会在前台显示')
            ;
        }

        return $fields;
    }
}
