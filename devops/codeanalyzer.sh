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
# Tools selector
PS3="which tool do you want to use? "
echo -e "Code Analizer for Magento2\n"; \
select tool in "${cmd[@]}"; do echo -e "\n  you selected ${tool}"'!'; break; done
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
    pathCode='app/code'
    pathDesign='app/design'
    ruleset='vendor/magtools/m2-core/devops/TestPR.xml'
    standar=vendor/magento/magento-coding-standard/Magento2
    fileName=$(printf "%s/%s_%s.txt\n" "${outputPath}" "${tool}" "WhyHasFailed")
    php ${cmdPath[testPR]} --standard=${standar} --severity=7 ${pathCode} ${pathDesign} > ${fileName} # | wc -l
    echo "PHP MESS DETECTOR " >> ${fileName}
    php ${cmdPath[testPRMD]} ${pathCode} text ${ruleset} >> ${fileName} # | wc -l
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
    select path in "${array[@]}"; do echo "you selected ${path}"'!'; break; done
    echo ''

    # Full path filename [path]/[tool]_[pool]_[package]
    IFS=/ read -a parts <<< "${path}"
    fileName=$(printf "%s/%s_%s_%s.txt\n" "${outputPath}" "${tool}" "${parts[-2]}" "${parts[-1]}")
    echo ''

    # PHPCS commands
    if [ "${tool}" = 'phpcs' ] ; then
      standar=vendor/magento/magento-coding-standard/Magento2
      php ${cmdPath[${tool}]} --standard=${standar} ${path} > ${fileName}
    fi

    # PHPCBF commands
    if [ "${tool}" = 'phpcbf' ] ; then
      standar=vendor/magento/magento-coding-standard/Magento2
      php ${cmdPath[${tool}]} --standard=${standar} ${path} > ${fileName}
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
        php ${cmdPath[${tool}]} ${path} ${mode} ${rulesetPath}/${rule}.xml >> ${fileName}
        echo ' ' >> ${fileName}
      done
    fi
fi

# Message with output location
echo "Processed path ${path} with ${tool}, you can find the output in ${fileName}"
echo ''
