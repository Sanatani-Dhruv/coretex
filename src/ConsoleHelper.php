<?php
namespace Coretex;

class ConsoleHelper {
    /*
    # Text Colors           Code
    # ---------------------------
    # Black                 0;30
    # White                 0;37
    # Red                   0;31
    # Green                 0;32
    # Brown                 0;33
    # Blue                  0;34
    # Magenta               0;35
    # Cyan                  0;36
    # Grey Bold             0;37

    # Background Colors     Code
    # ---------------------------
    # Black                 40
    # Red                   41
    # Green                 42
    # Yellow                43
    # Blue                  44
    # Magenta               45
    # Cyan                  46
    # Light Grey            47

    */

    private array $textColors;
    private array $backgroundColors;

    /**
     * Initializes the text and background colors with their ANSI escape codes.
     */
    public function __construct()
    {
        $this->textColors = [
            'black' => "\033[0;30",
            'white' => "\033[0;37",
            'red' => "\033[0;31",
            'green' => "\033[0;32",
            'yellow' => "\033[0;33",
            'blue' => "\033[0;34",
            'magenta' => "\033[0;35",
            'cyan' => "\033[0;36",
            'grey' => "\033[0;37",
            'reset' => "\033[0m"
        ];

        $this->backgroundColors = [
            'black' => "40m",
            'red' => "41m",
            'green' => "42m",
            'yellow' => "43m",
            'blue' => "44m",
            'magenta' => "45m",
            'cyan' => "46m",
            'light-grey' => "47m",
            'reset' => "\033[0m"
        ];
    }

    /**
     * Prints a message to the console with specified text and background colors.
     *
     * @param string $message The message to be printed.
     * @param string $textColor The text color to apply (key from textColors array).
     * @param string $backColor The background color to apply (key from backgroundColors array).
     *
     * @return void
     */
    public function print(string $message, string $textColor = '', string $backColor = '', bool $bold = false, bool $newLine = true): void
    {
        $colorMatch = '';
        $resetStr = '';

        if (isset($this->textColors[$textColor]) && isset($this->backgroundColors[$backColor])) {
            $colorMatch = $this->textColors[$textColor] . ";" . $this->backgroundColors[$backColor];
            $resetStr = $this->textColors['reset'];
        }

        if (isset($this->textColors[$textColor]) && !isset($this->backgroundColors[$backColor])) {
            $colorMatch = $this->textColors[$textColor] . "m";
            $resetStr = $this->textColors['reset'];
        }

        if ($bold) {
            $colorMatch = str_replace("[0;", "[1;", $colorMatch);
        }

        echo $colorMatch . $message . $resetStr . ($newLine ? PHP_EOL : '');
    }
}
