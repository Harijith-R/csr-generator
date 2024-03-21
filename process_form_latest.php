<?php

$baseDir = "/var/www/csr-pool/";
$timestamp = date("Y-m-d_H-i-s");
$prefix = "DNS:";
$successMessage = "CSR generated successfully!";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form input values
    $cname = $_POST["cname"];
    $orgname = $_POST["orgname"];
    $ouname = $_POST["ouname"];
    $locality = $_POST["locality"];
    $state = $_POST["state"];
    $country = $_POST["country"];
    $email   = $_POST["email"];
    $alternativeNamesInput = $_POST["alternativeNames"];
    $alternativeNamesArray = explode(",", $alternativeNamesInput);
    $modifiedAlternativeNamesArray = array_map(function ($value) use ($prefix) {
        return $prefix . trim($value);
    }, $alternativeNamesArray);
    $modifiedAlternativeNames = implode(", ", $modifiedAlternativeNamesArray);
    // Validate and sanitize the input values (You should implement proper validation/sanitization based on your requirements)
    if (strpos($cname, "*") !== false) {
	$prefix = "wildcard";
	$domain = str_replace("*", $prefix, $cname);
	$subj = "/C=" . $country . "/ST=" . $state . "/L=" . $locality . "/O=" . $orgname . "/OU=" . $ouname . "/CN=" . $cname . "/emailAddress=" . $email;
	$domfolder =  $baseDir . $domain;
	// Creating Domain folder
	if (!is_dir($domfolder)) 
	{
		if (mkdir($domfolder, 0755, true))
		{
		}
	}
	// Creating Date Folder
	$datefolder = "$domfolder/$timestamp";
	if (!is_dir($datefolder)) 
	{
    		if (mkdir($datefolder, 0755)) 
		{
		} 
		else 
		{
        		echo "Failed to create child directory.";
		}
	}
	$keyFilename = "$datefolder/$domain" . ".key";
	$csrFilename = "$datefolder/$domain" . ".csr";

	$command = "openssl req -new -newkey rsa:4096 -nodes -keyout " . $keyFilename . " -out " . $csrFilename . " -subj \"" . $subj . "\"";

	//$command = "openssl req -new -newkey rsa:4096 -nodes -keyout " . $keyFilename . " -out " . $csrFilename . " -subj \"" . $subj . " -addext " . "subjectAltName=" . $modifiedAlternativeNames . "\"";
    	$output = shell_exec($command);
	$csr_content = "cat $csrFilename";
	$key_content = "cat $keyFilename";
	$csr_out = shell_exec($csr_content);
	$key_out = shell_exec($key_content);
	echo "CSR Generated for: $domain on $timestamp\n";
	echo "$csr_out";
	$filename = "csr-$domain.txt";
        header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: application/octet-stream");
	header("Content-Length: " . strlen($csr_out));
    	} 
    else {
	    
	$domfolder =  $baseDir . $cname;
	$subj = "/C=" . $country . "/ST=" . $state . "/L=" . $locality . "/O=" . $orgname . "/OU=" . $ouname . "/CN=" . $cname . "/emailAddress=" . $email;
	// Creating Domain folder
        if (!is_dir($domfolder))
        {
                if (mkdir($domfolder, 0755, true))
                {
                }
        }
        // Creating Date Folder
        $datefolder = "$domfolder/$timestamp";
        if (!is_dir($datefolder))
        {
                if (mkdir($datefolder, 0755))
                {
                }
                else
                {
                        echo "Failed to create child directory.";
                }
        }
        $keyFilename = "$datefolder/$cname" . ".key";
        $csrFilename = "$datefolder/$cname" . ".csr";
	$command = "openssl req -new -newkey rsa:4096 -nodes -keyout " . $keyFilename . " -out " . $csrFilename . " -subj \"" . $subj . "\" -addext \"subjectAltName=" . $modifiedAlternativeNames . "\"";
	//$command = "openssl req -new -newkey rsa:4096 -nodes -keyout " . $keyFilename . " -out " . $csrFilename . " -subj \"" . $subj . " -addext " . "subjectAltName=" . $modifiedAlternativeNames . "\"";
	$output = shell_exec($command);
	$csr_content = "cat $csrFilename";
	$key_content = "cat $keyFilename";
	$csr_out = shell_exec($csr_content);
	$key_out = shell_exec($key_content);
	echo "CSR Generated for: $cname on $timestamp\n";
	echo "$csr_out";
	$filename = "csr-$cname.txt";
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: application/octet-stream");
	header("Content-Length: " . strlen($csr_out));

    }
}
//echo "Form submitted successfully!";
?>