<?php
/*

Generate documentation for PHPlot variables
   Copyright 2015 ljb - This is part of the PHPlot Reference Manual Source
   and may be used/distributed according to the same terms.
The input file is tab-separated fields with the first line being headers.
The required header fields (in any order) are:
   VARNAME DEFAULT REFERENCE DESCRIPTION
The input file is assumed to already be sorted, and output lines will be in
the same order.
There is no escaping, which allows for XML markup in the input.

*/

// Functions for output processing:

// Output the header
function output_header()
{
    echo <<<END

<!-- Note: This table was generated using a script. Do not edit. -->
<informaltable id="memvarlist">
  <tgroup cols="4">
    <?dbhtml cellpadding="5"?>
    <?dbhtml table-summary="PHPlot Member Variable Reference"?>
    <colspec colwidth="27*" />
    <colspec colwidth="12*" />
    <colspec colwidth="27*" />
    <colspec colwidth="34*" />
    <thead>
      <row>
        <entry>Variable Name:</entry>
        <entry>Default Value:</entry>
        <entry>Reference Function:</entry>
        <entry>Description:</entry>
      </row>
    </thead>
    <tbody>

END;
}

// Output one record
//   $d is an array indexed by field name.
function output_record($d)
{

    // Process the REFERENCE field. Unless empty or "(none)", this generates
    // a Docbook XML cross-reference link to each word in the field.
    $ref = $d["REFERENCE"];
    if (empty($ref) || $ref == "(none)") {
        $ref_col = "";
    } else {
        $ref_col_list = array();
        $r = strtok($ref, " \t");
        while ($r !== False) {
            if (!empty($r))
                $ref_col_list[] = "<xref linkend=\"$r\" />";
            $r = strtok(" \t");
        }
        $ref_col = implode(", ", $ref_col_list);
    }

    echo <<<END
      <row>
        <entry>{$d["VARNAME"]}</entry>
        <entry>{$d["DEFAULT"]}</entry>
        <entry>$ref_col</entry>
        <entry>{$d["DESCRIPTION"]}</entry>
      </row>

END;
}

// Output the footer
function output_footer()
{
    echo <<<END
    </tbody>
  </tgroup>
</informaltable>
<!-- End generated content -->

END;
}

// Read the next line from a file, ignoring blank and comment lines.
// Comment lines begin with # in the first column.
// Blank lines contain only whitespace.
// Returns False on end of file, else True
function getline($f, &$line)
{
    while (true) {
        $line = fgets($f);
        if ($line === False) return False;
        if (strlen($line) > 0 && $line[0] != '#') {
            $line = trim($line);
            if (!empty($line)) return True;
        }
    }
}

// Main processing:

// Read the headers:
if (!getline(STDIN, $line)) {
    fwrite(STDERR, "Error processing file: unable to read header\n");
    exit(1);
}
$headers = explode("\t", $line);
$n_headers = count($headers);

output_header();

// Read the file and process each record:
$n_rec = 0;
while (getline(STDIN, $line)) {
    $n_rec++;
    $d = array();
    // Assign $d[COL] to the corresponding field in the input line,
    // where the data fields are in the same order as the header fields.
    $fields = explode("\t", $line);
    for ($i = 0; $i < $n_headers; $i++) {
        $d[$headers[$i]] = $fields[$i];
    }
    output_record($d);
}
output_footer();
fwrite(STDERR, "Read and processed: $n_rec records\n");
