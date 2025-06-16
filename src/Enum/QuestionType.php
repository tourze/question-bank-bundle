<?php

declare(strict_types=1);

namespace Tourze\QuestionBankBundle\Enum;

enum QuestionType: string
{
    case SINGLE_CHOICE = 'single_choice';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';
    case FILL_BLANK = 'fill_blank';
    case ESSAY = 'essay';

    public function getLabel(): string
    {
        return match($this) {
            self::SINGLE_CHOICE => '单选题',
            self::MULTIPLE_CHOICE => '多选题',
            self::TRUE_FALSE => '判断题',
            self::FILL_BLANK => '填空题',
            self::ESSAY => '简答题',
        };
    }

    public function requiresOptions(): bool
    {
        return match($this) {
            self::SINGLE_CHOICE, self::MULTIPLE_CHOICE, self::TRUE_FALSE => true,
            self::FILL_BLANK, self::ESSAY => false,
        };
    }

    public function getMinOptions(): int
    {
        return match($this) {
            self::TRUE_FALSE => 2,
            self::SINGLE_CHOICE, self::MULTIPLE_CHOICE => 2,
            default => 0,
        };
    }

    public function getMaxOptions(): int
    {
        return match($this) {
            self::TRUE_FALSE => 2,
            self::SINGLE_CHOICE, self::MULTIPLE_CHOICE => 10,
            default => 0,
        };
    }

    /**
     * 获取题型对应的最少正确答案数量
     */
    public function getMinCorrectOptionCount(): int
    {
        return match ($this) {
            // 题型为【单选题】时，只能选择一个选项为正确答案
            // 题型为【多选题】时，至少要选择两个及以上选项为正确答案
            self::MULTIPLE_CHOICE => 2,
            default => 1,
        };
    }

    /**
     * 转换为数组格式
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
        ];
    }
}
