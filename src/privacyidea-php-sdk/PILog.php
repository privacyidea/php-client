<?php

/**
 * Interface PILog
 * Call the functions that collect debug and error messages
 */
interface PILog
{
    public function pi_debug($message);

    public function pi_error($message);
}