import os
import re

def analyze_weberp_sql_for_strict_mode(repo_path, selected_checks):
    """
    Analyzes PHP files in the webERP repository for SQL queries
    that might conflict with MariaDB's STRICT_TRANS_TABLES
    and ERROR_FOR_DIVISION_BY_ZERO SQL modes.
    Checks are performed based on 'selected_checks'.
    """
    php_files = []
    for root, _, files in os.walk(repo_path):
        for file in files:
            if file.endswith('.php'):
                php_files.append(os.path.join(root, file))

    print(f"Analyzing {len(php_files)} PHP files in '{repo_path}' for SQL mode conflicts...")

    potential_issues = {
        'strict_trans_tables_truncation': [],
        'strict_trans_tables_invalid_date': [],
        'error_for_division_by_zero': [],
        'non_full_group_by_columns': []
    }

    # Regex for common SQL query patterns. This is a simplification and not exhaustive.
    sql_patterns = re.compile(r'(?:INSERT|UPDATE|SELECT|DELETE)\s+.*?;', re.IGNORECASE | re.DOTALL)
    insert_update_pattern = re.compile(r'(INSERT\s+INTO|UPDATE)\s+.*?(VALUES|SET)\s+.*?;', re.IGNORECASE | re.DOTALL)
    select_division_pattern = re.compile(r'SELECT\s+.*\/\s*0.*', re.IGNORECASE)
    select_group_by_pattern = re.compile(r'SELECT\s+(.*?)\s+FROM\s+.*?GROUP BY\s+(.*?)(?:\s+HAVING|\s+ORDER BY|\s*;|$)', re.IGNORECASE | re.DOTALL)

    for file_path in php_files:
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()

                # Find all potential SQL queries
                queries = sql_patterns.findall(content)

                for query in queries:
                    # Check for potential data truncation/invalid values (STRICT_TRANS_TABLES)
                    if 'strict_trans_tables_truncation' in selected_checks and insert_update_pattern.search(query):
                        potential_issues['strict_trans_tables_truncation'].append(
                            f"File: {os.path.basename(file_path)}\n  Query (potential truncation/conversion issues with STRICT_TRANS_TABLES):\n  {query[:200]}..."
                        )

                    # Look for common invalid date patterns like '0000-00-00' being inserted
                    if 'strict_trans_tables_invalid_date' in selected_checks and re.search(r"\'0000-00-00(\s00:00:00)?\'", query):
                         potential_issues['strict_trans_tables_invalid_date'].append(
                            f"File: {os.path.basename(file_path)}\n  Query (potential invalid date issue with STRICT_TRANS_TABLES):\n  {query[:200]}..."
                        )

                    # Check for division by zero (ERROR_FOR_DIVISION_BY_ZERO)
                    if 'error_for_division_by_zero' in selected_checks and select_division_pattern.search(query):
                        potential_issues['error_for_division_by_zero'].append(
                            f"File: {os.path.basename(file_path)}\n  Query (potential division by zero issue):\n  {query[:200]}..."
                        )

                    # Check for non-standard GROUP BY (if ONLY_FULL_GROUP_BY was expected, but it's not in your new SQL_MODE)
                    if 'non_full_group_by_columns' in selected_checks:
                        match_group_by = select_group_by_pattern.search(query)
                        if match_group_by:
                            selected_columns_str = match_group_by.group(1).strip()
                            group_by_columns_str = match_group_by.group(2).strip()

                            selected_columns = [col.strip() for col in selected_columns_str.split(',')]
                            group_by_columns = [col.strip() for col in group_by_columns_str.split(',')]

                            for col in selected_columns:
                                if not any(agg_func in col.upper() for agg_func in ['SUM(', 'COUNT(', 'AVG(', 'MAX(', 'MIN(']) \
                                   and col not in group_by_by_columns:
                                    potential_issues['non_full_group_by_columns'].append(
                                        f"File: {os.path.basename(file_path)}\n  Query (potential non-deterministic GROUP BY results with ONLY_FULL_GROUP_BY OFF):\n  {query[:200]}..."
                                    )
                                    break # only report once per query


        except Exception as e:
            print(f"Could not read or parse file {file_path}: {e}")

    print("\n--- SQL Query Analysis Results for PTADU-webERP ---")
    print("Based on SQL_MODE: STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION")

    if 'strict_trans_tables_truncation' in selected_checks:
        if potential_issues['strict_trans_tables_truncation']:
            print("\n### Potential Data Truncation/Conversion Issues (STRICT_TRANS_TABLES) ###")
            print("Queries where data might be implicitly truncated or converted, leading to errors with STRICT_TRANS_TABLES:")
            for issue in sorted(list(set(potential_issues['strict_trans_tables_truncation']))): # Sort for consistent output
                print(f"- {issue}\n")
            print("Action: Thoroughly test all data entry and update forms. Ensure input data types and lengths match database column definitions. Consider explicitly casting values where necessary.")
        else:
            print("\nNo explicit problematic queries found for STRICT_TRANS_TABLES (truncation/conversion), but extensive testing is still advised.")

    if 'strict_trans_tables_invalid_date' in selected_checks:
        if potential_issues['strict_trans_tables_invalid_date']:
            print("\n### Potential Invalid Date Issues (STRICT_TRANS_TABLES) ###")
            print("Queries attempting to insert '0000-00-00' or similar invalid dates, which will error with STRICT_TRANS_TABLES:")
            for issue in sorted(list(set(potential_issues['strict_trans_tables_invalid_date']))):
                print(f"- {issue}\n")
            print("Action: Modify application logic to use NULL or a valid default date (e.g., '1970-01-01') instead of '0000-00-00'.")
        else:
            print("No explicit '0000-00-00' date insertions found by this script, but check for other invalid date patterns.")

    if 'error_for_division_by_zero' in selected_checks:
        if potential_issues['error_for_division_by_zero']:
            print("\n### Potential Division By Zero Issues (ERROR_FOR_DIVISION_BY_ZERO) ###")
            print("Queries where division by zero might occur, now leading to errors:")
            for issue in sorted(list(set(potential_issues['error_for_division_by_zero']))):
                print(f"- {issue}\n")
            print("Action: Review these queries. Implement `NULLIF(divisor, 0)` or `CASE` statements to handle zero divisors gracefully (e.g., return NULL or 0).")
        else:
            print("\nNo explicit division by zero patterns found by this script.")

    if 'non_full_group_by_columns' in selected_checks:
        if potential_issues['non_full_group_by_columns']:
            print("\n### Potential Non-Deterministic GROUP BY Results (ONLY_FULL_GROUP_BY is OFF) ###")
            print("Queries that select non-aggregated columns not in the GROUP BY clause. While your new SQL_MODE allows this (as ONLY_FULL_GROUP_BY is OFF), results might be non-deterministic:")
            for issue in sorted(list(set(potential_issues['non_full_group_by_columns']))):
                print(f"- {issue}\n")
            print("Action: Review these queries to ensure they produce the expected results. For deterministic behavior, all non-aggregated columns in the SELECT list should be either in the GROUP BY clause or part of a functional dependency on the GROUP BY columns.")
        else:
            print("\nNo obvious non-standard GROUP BY queries found by this script.")

    print("\n--- Additional Considerations for Migration ---")
    print("1.  **Database Collation:** Verify that your database and table collations are appropriate for UTF-8 (e.g., `utf8mb4_unicode_ci` or `utf8mb4_general_ci`) if you store multi-byte characters. Changes in default collation can lead to sorting or comparison issues.")
    print("2.  **MySQLa_DB_Query() and DB_query()**: webERP encapsulates database interactions. Look closely at how these functions are used, especially with complex WHERE clauses or data manipulations.")
    print("3.  **Full Regression Testing:** The most critical step is to perform extensive testing on a non-production environment after migration. This script is a preliminary check, not a guarantee.")

if __name__ == "__main__":
    print("webERP SQL Query Strict Mode Pre-Check Script")
    print("---------------------------------------------")

    # Prompt user for repository path
    repo_path = input(r"Enter the absolute path to your unzipped PTADU-webERP folder (e.g., C:\PTADU-webERP): ")

    if not os.path.isdir(repo_path):
        print(f"Error: The path '{repo_path}' is not a valid directory. Please try again.")
    else:
        print("\nSelect the types of potential issues to check (enter numbers separated by commas):")
        print("1. Potential Data Truncation/Conversion (STRICT_TRANS_TABLES)")
        print("2. Potential Invalid Date Issues (STRICT_TRANS_TABLES)")
        print("3. Potential Division By Zero Issues (ERROR_FOR_DIVISION_BY_ZERO)")
        print("4. Potential Non-Deterministic GROUP BY Results")
        print("   (Note: Your new SQL_MODE allows this, but results might be inconsistent.)")
        print("Enter 1, 2, 3, 4, or any combination (e.g., '1,3'): ")

        choices_input = input("Your choice(s): ")
        choices = [c.strip() for c in choices_input.split(',')]
        
        selected_checks = []
        if '1' in choices:
            selected_checks.append('strict_trans_tables_truncation')
        if '2' in choices:
            selected_checks.append('strict_trans_tables_invalid_date')
        if '3' in choices:
            selected_checks.append('error_for_division_by_zero')
        if '4' in choices:
            selected_checks.append('non_full_group_by_columns')
        
        if not selected_checks:
            print("No valid check types selected. Exiting.")
        else:
            analyze_weberp_sql_for_strict_mode(repo_path, selected_checks)

    input("\nPress Enter to exit...")