<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\QuestionBankBundle\Controller\Admin\CategoryCrudController;
use Tourze\QuestionBankBundle\Controller\Admin\OptionCrudController;
use Tourze\QuestionBankBundle\Controller\Admin\QuestionCrudController;
use Tourze\QuestionBankBundle\Controller\Admin\TagCrudController;
use Tourze\QuestionBankBundle\Repository\CategoryRepository;
use Tourze\QuestionBankBundle\Repository\QuestionRepository;
use Tourze\QuestionBankBundle\Repository\TagRepository;

/**
 * AdminMenu 服务
 *
 * 为后台管理系统提供题库管理菜单项
 */
final class AdminMenu implements MenuProviderInterface
{
    private QuestionRepository $questionRepository;

    private CategoryRepository $categoryRepository;

    private TagRepository $tagRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
    ) {
        $this->questionRepository = $questionRepository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
    }

    public function __invoke(ItemInterface $item): void
    {
        $questionBankMenu = $item->addChild('题库管理', [
            'extras' => [
                'icon' => 'fas fa-question-circle',
                'order' => 100,
            ],
        ]);

        $questionBankMenu->addChild('问题管理', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => QuestionCrudController::class],
            'extras' => [
                'icon' => 'fas fa-question',
                'badge' => $this->getQuestionCount(),
            ],
        ]);

        $questionBankMenu->addChild('分类管理', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => CategoryCrudController::class],
            'extras' => [
                'icon' => 'fas fa-folder',
                'badge' => $this->getCategoryCount(),
            ],
        ]);

        $questionBankMenu->addChild('标签管理', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => TagCrudController::class],
            'extras' => [
                'icon' => 'fas fa-tags',
                'badge' => $this->getTagCount(),
            ],
        ]);

        $questionBankMenu->addChild('选项管理', [
            'route' => 'admin',
            'routeParameters' => ['crudAction' => 'index', 'crudControllerFqcn' => OptionCrudController::class],
            'extras' => [
                'icon' => 'fas fa-list-ul',
            ],
        ]);
    }

    /**
     * 获取菜单配置
     *
     * @return array<string, mixed>
     */
    public function getMenuItems(): array
    {
        return [
            'question_bank' => [
                'label' => '题库管理',
                'icon' => 'fas fa-question-circle',
                'group' => '内容管理',
                'order' => 100,
                'badge' => $this->getQuestionCount(),
                'submenu' => [
                    'question_management' => [
                        'label' => '问题管理',
                        'controller' => QuestionCrudController::class,
                        'icon' => 'fas fa-question',
                        'badge' => $this->getQuestionCount(),
                    ],
                    'category_management' => [
                        'label' => '分类管理',
                        'controller' => CategoryCrudController::class,
                        'icon' => 'fas fa-folder',
                        'badge' => $this->getCategoryCount(),
                    ],
                    'tag_management' => [
                        'label' => '标签管理',
                        'controller' => TagCrudController::class,
                        'icon' => 'fas fa-tags',
                        'badge' => $this->getTagCount(),
                    ],
                    'option_management' => [
                        'label' => '选项管理',
                        'controller' => OptionCrudController::class,
                        'icon' => 'fas fa-list-ul',
                    ],
                ],
            ],
        ];
    }

    /**
     * 获取问题总数（用于菜单徽章显示）
     */
    private function getQuestionCount(): int
    {
        try {
            return $this->questionRepository->count(['valid' => true]);
        } catch (\Exception $e) {
            // 如果获取失败，返回0，避免影响菜单显示
            return 0;
        }
    }

    /**
     * 获取分类总数（用于菜单徽章显示）
     */
    private function getCategoryCount(): int
    {
        try {
            return $this->categoryRepository->count(['valid' => true]);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取标签总数（用于菜单徽章显示）
     */
    private function getTagCount(): int
    {
        try {
            return $this->tagRepository->count(['valid' => true]);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取统计信息
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        try {
            $questionStats = $this->getQuestionStatistics();
            $categoryStats = $this->getCategoryStatistics();
            $tagStats = $this->getTagStatistics();

            return [
                'questions' => $questionStats,
                'categories' => $categoryStats,
                'tags' => $tagStats,
                'summary' => [
                    'total_questions' => $questionStats['total'],
                    'published_questions' => $questionStats['published'],
                    'draft_questions' => $questionStats['draft'],
                    'total_categories' => $categoryStats['total'],
                    'total_tags' => $tagStats['total'],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'questions' => ['total' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0],
                'categories' => ['total' => 0, 'active' => 0],
                'tags' => ['total' => 0, 'active' => 0],
                'summary' => ['total_questions' => 0, 'published_questions' => 0, 'draft_questions' => 0, 'total_categories' => 0, 'total_tags' => 0],
            ];
        }
    }

    /**
     * 获取问题统计
     *
     * @return array<string, int>
     */
    private function getQuestionStatistics(): array
    {
        return [
            'total' => $this->questionRepository->count([]),
            'published' => $this->questionRepository->count(['status' => 'published']),
            'draft' => $this->questionRepository->count(['status' => 'draft']),
            'archived' => $this->questionRepository->count(['status' => 'archived']),
        ];
    }

    /**
     * 获取分类统计
     *
     * @return array<string, int>
     */
    private function getCategoryStatistics(): array
    {
        return [
            'total' => $this->categoryRepository->count([]),
            'active' => $this->categoryRepository->count(['valid' => true]),
        ];
    }

    /**
     * 获取标签统计
     *
     * @return array<string, int>
     */
    private function getTagStatistics(): array
    {
        return [
            'total' => $this->tagRepository->count([]),
            'active' => $this->tagRepository->count(['valid' => true]),
        ];
    }

    /**
     * 获取面板小部件配置
     *
     * @return array<string, mixed>
     */
    public function getDashboardWidget(): array
    {
        $stats = $this->getStatistics();

        return [
            'title' => '题库管理',
            'template' => '@QuestionBank/admin/widget/dashboard.html.twig',
            'data' => $stats,
            'priority' => 200,
        ];
    }
}
