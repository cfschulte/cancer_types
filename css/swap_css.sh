#!/bin/bash
# swap_css.sh:  Wed Dec 19 14:26:39 CST 2018
# 

if [ -f style_new.css ]
then
        if [ -f style.css ]
        then
                echo "move style to style old"
                mv style.css style_old.css
                echo "move phsics_new to style"
                mv style_new.css style.css
        fi
elif [ -f style_old.css ]
then
        if [ -f style.css ]
        then
                echo "move style to style_new"
                mv style.css style_new.css
                echo "move style_old to style"
                mv style_old.css style.css
        fi
fi
