#!/bin/bash
#
# Copy this file to the magento root
#
# Output path
outputPath=var/static
mkdir -p ${outputPath}
echo ''

# Tools paths
declare -A cmdPath
cmdPath[phpcs]=vendor/squizlabs/php_codesniffer/bin/phpcs;
cmdPath[phpcbf]=vendor/squizlabs/php_codesniffer/bin/phpcbf;
cmdPath[phpmd]=vendor/phpmd/phpmd/src/bin/phpmd;
cmdPath[testPR]=vendor/squizlabs/php_codesniffer/bin/phpcs;
cmdPath[testPRMD]=vendor/phpmd/phpmd/src/bin/phpmd;
cmdPath[HELP]=showHelp;
# Tools names
cmd=(
phpcs
phpcbf
phpmd
testPR
HELP
);

# Error found flag
errorFound=0

# Tools selector
PS3="which tool do you want to use? "
echo -e "Code Analizer for Magento2\n"; \
select tool in "${cmd[@]}"; do echo -e "\n  running ${tool}"'!'; break; done
echo ''

### Check for HELP and TEST first
if [ "${tool}" = 'HELP' ]; then
    echo '  HELP codeanalyzer.sh';
    echo '';
    echo '    phpcs     PHP Code Sniffer with Magento2 standard';
    echo '    phpcbf    PHP Code Fixer (for code sniffer [x] marked issues)';
    echo '    phpmd     PHP Mess Detector with Magento2 standard';
    echo '    testPR    Test pull request code, why has failed?';
    echo '';
    exit 1;
elif [ "${tool}" = 'testPR' ] ; then
    # jenkins pipeline checks for phpcs serverity >= 7
    if [ ! -f app/devops/TestPR.xml ]; then
        echo -e "\033[0;31m  Ruleset file not found! \033[0m"
        echo -e "\033[0;31m  Please copy the file from Mtools_Core: \033[0m"
        echo -e "\033[0;31m  magtools/m2-core/devops/TestPR.xml to app/devops/TestPR.xml and try again \033[0m"
        echo " "
        exit 1;
    else
        pathCode='app/code'
        pathDesign='app/design'
        ruleset='app/devops/TestPR.xml'
        standar=vendor/magento/magento-coding-standard/Magento2
        fileName=$(printf "%s/%s_%s.txt\n" "${outputPath}" "${tool}" "WhyHasFailed")
        if ! php ${cmdPath[testPR]} --ignore=*/Test/Unit/* --standard=${standar} --severity=7 ${pathCode} ${pathDesign} > ${fileName}; then
            let errorFound++
        fi
        echo "PHP MESS DETECTOR " >> ${fileName}
        if ! php ${cmdPath[testPRMD]} ${pathCode} text ${ruleset} >> ${fileName}; then
            let errorFound++
        fi
    fi
else
    # Code paths to analyze
    cod=(app/code/*/);
    mod=(app/code/*/*/);
    ext=(extensions/*/); # local composer repository for 3rd party modules
    front=(app/design/frontend/*/);
    admin=(app/design/adminhtml/*/);
    array+=("${cod[@]}" "${mod[@]}" "${front[@]}" "${admin[@]}" "${ext[@]}")
    # Code path selector
    PS3="which path do you want to process? "
    echo "There are ${#array[@]} available paths to process"; \
    select path in "${array[@]}"; do echo -e "\n  running ${tool} on ${path}"'...'; break; done
    echo ''

    # Full path filename [path]/[tool]_[pool]_[package]
    IFS=/ read -a parts <<< "${path}"
    fileName=$(printf "%s/%s_%s_%s.txt\n" "${outputPath}" "${tool}" "${parts[-2]}" "${parts[-1]}")
    echo ''

    # PHPCS commands
    if [ "${tool}" = 'phpcs' ] ; then
      standar=vendor/magento/magento-coding-standard/Magento2
      if ! php ${cmdPath[${tool}]} --ignore=*/Test/Unit/* --standard=${standar} ${path} > ${fileName}; then
          let errorFound++
      fi
    fi

    # PHPCBF commands
    if [ "${tool}" = 'phpcbf' ] ; then
      standar=vendor/magento/magento-coding-standard/Magento2
      if ! php ${cmdPath[${tool}]} --ignore=*/Test/Unit/* --standard=${standar} ${path} > ${fileName}; then
          let errorFound++
      fi
    fi

    # PHPMD commands
    if [ "${tool}" = 'phpmd' ] ; then
      mode=text
      rulesetPath=vendor/phpmd/phpmd/src/main/resources/rulesets
      ruleset=(cleancode codesize controversial design naming unusedcode);
      echo ' ' > ${fileName}
      for rule in "${ruleset[@]}"
      do :
        echo ' '${rule} >> ${fileName}
        echo '============================================================' >> ${fileName}
        if ! php ${cmdPath[${tool}]} ${path} ${mode} ${rulesetPath}/${rule}.xml --exclude */Unit/*Test.php >> ${fileName}; then
            let errorFound++
        fi
        echo ' ' >> ${fileName}
      done
    fi
fi

# Message with output location
if [ ${errorFound} -gt 0 ]; then
    echo -e "\033[0;31m  Some issues were found, please check at the output file \033[0m"
else
    echo -e "\033[0;32m  No issues were found! \033[0m"
fi
echo ''
echo "  Processed path ${path} with ${tool}, you can find the output in ${fileName}"
echo ''
