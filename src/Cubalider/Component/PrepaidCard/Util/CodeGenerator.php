<?php

namespace Cubalider\Component\PrepaidCard\Util;

/**
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 */
class CodeGenerator implements CodeGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate($pattern = 'xxxx-xxxx-xxxx', $divider = '-')
    {
        $i = 0;
        $length = strlen($pattern);
        while ($i < $length) {
            $pattern = preg_replace('/x/', rand(0, 9), $pattern, 1);
            $i++;
        }
        $code = str_replace('-', $divider, $pattern);

        return $code;
    }
}