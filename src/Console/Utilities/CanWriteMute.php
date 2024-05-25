<?php

namespace MahdiAslami\Deployer\Console\Utilities;

trait CanWriteMute
{
    protected function preserveCursorPosition(callable $callback)
    {
        // Save the cursor position
        $this->output->write("\033[s");

        call_user_func($callback);

        // Restore the cursor position
        $this->output->write("\033[u");
    }

    protected function mute(string $text)
    {
        $output = $this->removeAnsiEscapeCodes($text);
        $output = $this->addLeftPadding($output);

        $this->output->write(
            '<fg=gray>'
            . $output
            . "</>\n"
        );
    }

    private function removeAnsiEscapeCodes(string $text)
    {
        $moveToFirstColumnOfCurrentLine = "\033\[[\d;]*G";
        $output = preg_replace('/' . $moveToFirstColumnOfCurrentLine . '/', "\n", $text);

        $allMovementAndColor = "\033\[[\d;]*[a-zA-Z]";
        $colorTag = '<[^>]*>';
        return preg_replace('/' . $allMovementAndColor . '|' . $colorTag . '/', '', $output);
    }

    private function addLeftPadding(string $text)
    {
        return preg_replace('/^(.*)$/m', '-- $1', $text);
    }
}
