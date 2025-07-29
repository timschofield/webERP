import mysql.connector
from mysql.connector import Error

def check_mariadb_for_migration_issues(host, database, user, password):
    """
    Connects to a MariaDB database and checks for potential migration issues
    from MariaDB 5.x to 10.3.
    """
    conn = None
    try:
        conn = mysql.connector.connect(
            host=host,
            database=database,
            user=user,
            password=password
        )

        if conn.is_connected():
            print(f"Successfully connected to MariaDB database: {database}")

            cursor = conn.cursor()

            # 1. Check for MyISAM tables
            print("\n--- Checking for MyISAM Tables ---")
            cursor.execute("""
                SELECT
                    table_schema,
                    table_name,
                    engine
                FROM
                    information_schema.tables
                WHERE
                    engine = 'MyISAM' AND table_schema = %s;
            """, (database,))
            myisam_tables = cursor.fetchall()

            if myisam_tables:
                print("Potential Issue: The following tables use the MyISAM storage engine.")
                print("It is highly recommended to convert these to InnoDB for better performance,")
                print("data integrity (ACID compliance), and crash recovery in MariaDB 10.3.")
                print("Consider running ALTER TABLE your_table_name ENGINE=InnoDB; for each:")
                for schema, table_name, engine in myisam_tables:
                    print(f"  - Database: {schema}, Table: {table_name}, Engine: {engine}")
            else:
                print("No MyISAM tables found. All tables appear to be using InnoDB or other compatible engines.")

            # 2. Check current SQL_MODE (this might be system-wide or session-specific)
            print("\n--- Checking Current SQL_MODE ---")
            cursor.execute("SELECT @@sql_mode;")
            sql_mode = cursor.fetchone()[0]
            print(f"Current SQL_MODE: {sql_mode}")
            if 'STRICT_TRANS_TABLES' not in sql_mode and 'STRICT_ALL_TABLES' not in sql_mode:
                print("Warning: Your current SQL_MODE is not strict. MariaDB 10.3 might default")
                print("to a stricter mode (e.g., including STRICT_TRANS_TABLES or TRADITIONAL).")
                print("This could cause issues with queries that rely on implicit type conversions,")
                print("inserting invalid data, or truncating values without error.")
                print("Test your application thoroughly with a stricter SQL_MODE.")
            else:
                print("SQL_MODE appears to be sufficiently strict. Less likely to encounter new strictness issues.")

            # 3. Basic check for specific old data types (less common but good to mention)
            # This is harder to automate generally, but specific types could be checked.
            # For simplicity, we'll just advise manual review.
            print("\n--- Manual Review Advised ---")
            print("Review your database schema for deprecated or problematic data types or functions.")
            print("Examples include very old temporal types usage or specific functions.")
            print("You can get a full schema dump using:")
            print(f"  mysqldump -u {user} -p {database} --no-data > {database}_schema.sql")
            print("Then review the .sql file for any unusual or very old definitions.")

    except Error as e:
        print(f"Error connecting to MariaDB or executing queries: {e}")
        print("Please ensure your database is running, credentials are correct,")
        print("and the database user has sufficient permissions.")
    finally:
        if conn and conn.is_connected():
            cursor.close()
            conn.close()
            print("\nMariaDB connection closed.")

if __name__ == "__main__":
    print("MariaDB Migration Pre-Check Script (v5.x to 10.3)")
    print("--------------------------------------------------")

#    db_host = input("Enter your MariaDB host (e.g., localhost, 127.0.0.1): ")
#    db_name = input("Enter your MariaDB database name (e.g., weberp): ")
#    db_user = input("Enter your MariaDB username: ")
#    db_password = input("Enter your MariaDB password: ")

    print("\nPRODUCTION SERVER.")
    db_host = "202.157.184.151"
    db_name = "kurakura_kl_erp"
    db_user = "kurakura_kl_0001"
    db_password = "KXGrwKrlKduQTSdqnLZc"

    check_mariadb_for_migration_issues(db_host, db_name, db_user, db_password)
    print("\nPre-check complete. Please review the output carefully.")

    print("\nTEST SERVER.")
    db_host = "103.229.75.83"
    db_name = "kurakura_kl_erp"
    db_user = "kurakura_kl_0001"
    db_password = "KXGrwKrlKduQTSdqnLZc"

    check_mariadb_for_migration_issues(db_host, db_name, db_user, db_password)


    input("Press Enter to exit...")