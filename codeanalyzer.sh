#!/bin/bash

# Output path
outputPath=var/static
mkdir -p ${outputPath}
echo ''

# Tools paths
declare -A cmdPath
cmdPath[phpcs]=vendor/squizlabs/php_codesniffer/bin/phpcs;
cmdPath[phpcbf]=vendor/squizlabs/php_codesniffer/bin/phpcbf;
cmdPath[phpmd]=vendor/phpmd/phpmd/src/bin/phpmd;
# Tools names
cmd=(
phpcs
phpcbf
phpmd
);
# Tools selector
PS3="which tool do you want to use? "
echo "There are ${#cmd[@]} available tools"; \
select tool in "${cmd[@]}"; do echo "you selected ${tool}"'!'; break; done
echo ''

# Code paths to analyze
mod=(app/code/*/); # you can add vendor name for individual module listing mod=(app/code/[vendor]/*/);
ext=(extensions/*/); # local composer repository for 3rd party modules
front=(app/design/frontend/*/);
admin=(app/design/adminhtml/*/);
array+=("${mod[@]}" "${front[@]}" "${admin[@]}" "${ext[@]}")
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

# Message with output location
echo "Processed path ${path} with ${tool}, you can find the output in ${fileName}"
echo ''
