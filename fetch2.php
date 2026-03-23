<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

\ = [
    "Orbit Computer", "Fourcubes Industry LLP", "GARUDA ADVERTISING PRIVATE LIMITED",
    "BURGAR ADVERTISING PRIVATE LIMITED", "SILICONVEINS PRIVATE LIMITED",
    "VANTAGE CHEMICAL INDUSTRIES PRIVATE LIMITED", "Nirex Chemicals India Limited",
    "Nilkantha Chemical Industries Private Limited", "Devam Pharma",
    "Cohizon Life Sciences Limited", "ANOKHI DAWA NI DUKAN", "AMRUT JEWELLERS",
    "Mahavir Cardeal Private Limited", "Mahansaria Tyres Pvt Ltd", "Ekon Abrasives",
    "Global S.S. Construction Pvt LTD", "Shakti Chem", "Divine E Waste Solution",
    "Assert Secure Tech Pvt Ltd", "Swastik Solutions", "Pioneer Systems",
    "Techno Systems", "Sysmac IT Solutions Private Limited", "Tuv India Pvt Ltd",
    "Kewaunee Labway India Pvt.Ltd.", "Log in Computers", "Laptop Doctor",
    "FRIENDS MOBILE", "JAY AUTO AGENCY", "ACE PIPELINE CONSTRACTS PVT LTD"
];

function fetch_url(\) {
    if (!extension_loaded('curl')) return [500, false];
    \ = curl_init();
    curl_setopt(\, CURLOPT_URL, \);
    curl_setopt(\, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(\, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt(\, CURLOPT_TIMEOUT, 6);
    curl_setopt(\, CURLOPT_FOLLOWLOCATION, true);
    \ = curl_exec(\);
    \ = curl_getinfo(\, CURLINFO_HTTP_CODE);
    curl_close(\);
    return [\, \];
}

function extract_domain(\) {
    \ = "https://html.duckduckgo.com/html/?q=" . urlencode(\ . " official website");
    list(\, \) = fetch_url(\);
    if (\ == 200 && \) {
        if (preg_match('/uddg=https?%3A%2F%2F(?:www\.)?([^%&]+)/i', \, \)) {
            \ = trim(\[1]);
            return \;
        }
    }
    return null;
}

if (!is_dir('images/logos/clients2')) {
    mkdir('images/logos/clients2', 0777, true);
}

\ = [];
foreach (\ as \) {
    \ = extract_domain(\);
    \ = false;
    \ = preg_replace('/[^a-zA-Z0-9]/', '_', \);

    if (\) {
        \ = ['zauba', 'indiamart', 'justdial', 'linkedin', 'facebook', 'tradeindia', 'jd', 'glassdoor', 'duckduckgo', 'justdial.com'];
        \ = false;
        foreach (\ as \) {
            if (stripos(\, \) !== false) \ = true;
        }
        if (!\) {
            \ = "https://logo.clearbit.com/\";
            list(\, \) = fetch_url(\);
            if (\ == 200 && \ && strlen(\) > 0) {
                file_put_contents("images/logos/clients2/\.png", \);
                \ = "images/logos/clients2/\.png";
            }
        }
    }
    
    \[] = ['name' => \, 'logo' => \];
}

file_put_contents('clients_data.json', json_encode(\, JSON_PRETTY_PRINT));
echo "Done.";
?>
