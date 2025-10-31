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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;
use Tourze\QuestionBankBundle\Entity\Question;
use Tourze\QuestionBankBundle\Enum\QuestionStatus;
use Tourze\QuestionBankBundle\Enum\QuestionType;

#[AdminCrud(routePath: '/question-bank/question', routeName: 'question_bank_question')]
final class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('问题')
            ->setEntityLabelInPlural('问题管理')
            ->setPageTitle('index', '问题列表')
            ->setPageTitle('new', '新增问题')
            ->setPageTitle('edit', '编辑问题')
            ->setPageTitle('detail', '问题详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['title', 'content'])
            ->setPaginatorPageSize(20)
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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('type')
            ->add('difficulty')
            ->add('status')
            ->add('valid')
            ->add('categories')
            ->add('tags')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('title', '问题标题')
                ->setColumns(12)
                ->setRequired(true)
                ->setHelp('问题的简要标题'),
        ];

        if (Crud::PAGE_INDEX === $pageName) {
            $typeField = EnumField::new('type', '题目类型');
            $typeField->setEnumCases(QuestionType::cases());
            $typeField->setColumns(2);
            $fields[] = $typeField;
            $fields[] = IntegerField::new('difficultyLevel', '难度')
                ->setColumns(1)
            ;
            $fields[] = MoneyField::new('score', '分数')
                ->setCurrency('CNY')
                ->setStoredAsCents(false)
                ->setColumns(2)
            ;
            $statusField = EnumField::new('status', '状态');
            $statusField->setEnumCases(QuestionStatus::cases());
            $statusField->setColumns(2);
            $fields[] = $statusField;
            $fields[] = BooleanField::new('valid', '有效')
                ->setColumns(1)
            ;
            $fields[] = AssociationField::new('categories', '分类')
                ->setColumns(2)
            ;
            $fields[] = AssociationField::new('tags', '标签')
                ->setColumns(2)
            ;
        } else {
            $fields[] = TextareaField::new('content', '问题内容')
                ->setColumns(12)
                ->setRequired(true)
                ->setHelp('问题的详细内容描述')
                ->setNumOfRows(4)
            ;

            $typeField = EnumField::new('type', '题目类型');
            $typeField->setEnumCases(QuestionType::cases());
            $typeField->setColumns(6);
            $typeField->setRequired(true);
            $typeField->setHelp('选择题目类型');
            $fields[] = $typeField;

            $fields[] = IntegerField::new('difficultyLevel', '难度级别')
                ->setColumns(6)
                ->setRequired(true)
                ->setHelp('难度范围：1-5，数字越大越难')
            ;

            $fields[] = MoneyField::new('score', '分数')
                ->setCurrency('CNY')
                ->setStoredAsCents(false)
                ->setColumns(6)
                ->setRequired(true)
                ->setHelp('该题的分值')
            ;

            $statusField = EnumField::new('status', '问题状态');
            $statusField->setEnumCases(QuestionStatus::cases());
            $statusField->setColumns(6);
            $statusField->setHelp('问题的发布状态');
            $fields[] = $statusField;

            $fields[] = AssociationField::new('categories', '所属分类')
                ->setColumns(6)
                ->setRequired(false)
                ->setHelp('可以选择多个分类')
                ->autocomplete()
            ;

            $fields[] = AssociationField::new('tags', '标签')
                ->setColumns(6)
                ->setRequired(false)
                ->setHelp('可以选择多个标签')
                ->autocomplete()
            ;

            $fields[] = TextareaField::new('explanation', '答案解析')
                ->setColumns(12)
                ->setRequired(false)
                ->setHelp('对正确答案的解释说明')
                ->setNumOfRows(3)
            ;

            $fields[] = BooleanField::new('valid', '是否有效')
                ->setColumns(6)
                ->setHelp('无效的问题将不会在前台显示')
            ;
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            $fields[] = TextField::new('id', 'ID')
                ->setColumns(6)
            ;
            $fields[] = TextareaField::new('content', '问题内容')
                ->setColumns(12)
            ;
            $fields[] = TextareaField::new('explanation', '答案解析')
                ->setColumns(12)
            ;
            $fields[] = AssociationField::new('options', '选项')
                ->setColumns(12)
            ;
            $fields[] = TextField::new('metadata', '扩展元数据')
                ->setColumns(12)
            ;
        }

        return $fields;
    }
}
