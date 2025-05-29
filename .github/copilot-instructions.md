---
applyTo: "**"
---
# Copilot Instructions
# This file contains instructions for GitHub Copilot to follow when generating code.

General prompt rules applying to PHP, CCS, and JS related files: 
# 1) Do not cut long lines, unless specificaly defined in the user prompt.
2) Do not delete any comment in the code, unless it contains wrong information or it is belonging to the code you are deleting.
3) Do not add comments similar to //Added lines, //Removed lines, //Modified lines, etc.
4) Do not change, modify, upgrade any other part of the code not related to the propmt.
5) Trailing whitespaces: Remove all trailing whitespaces
6) Indentation: Convert indentation spaces into tab with size 1 tab = 4 spaces.
7) PascalCase: All variables must be written in PascalCase style and fix the wrong ones. Except variables starting with $id*, $webERP*, $SQL*, $HTML* or counters like $i, $j.
8) Binary Operators: ensure are surrounded by one space on each side
9) Unary Operators: ensure no spaces after the operators
10) Ternary Operators: Must have a space before and after the ? and the :
11) Function Calls: No space between the function name and the opening parenthesis. No space after the opening parenthesis or before the closing parenthesis.
12) Line Length: For any line longer than 120 characters divide it into several lines to improve readability.
13) Fix any potential Divide By Zero error.

