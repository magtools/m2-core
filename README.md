# m2-core
Magento 2 core module

## codeanalyzer.sh
Copy the file to the magento root and perform fast static code reviews.

$ bash codeanalyzer.sh

    There are 3 available tools
    1) phpcs
    2) phpcbf
    3) phpmd
    which tool do you want to use? 3
    you selected phpmd!
    
    There are 5 available paths to process
    1) app/code/Example/Core/    
    2) app/code/Example/Setup/    
    3) app/design/frontend/Example/  
    4) app/design/adminhtml/Example/ 
    5) extensions/magtools/
    which path do you want to process? 1
    you selected app/code/Example/Core/!
    
    Processed path app/code/Example/Core/ with phpmd, you can find the output in var/static/phpmd_Example_Core.txt

## path extensions
The codeanalizer.sh utility references to an extensions path, this is an optional path for local composer repository.

    "repositories": [
        {
            "type": "composer",
            "url": "https://repo.magento.com/"
        },
        {
            "type": "path",
            "url": "extensions/*/*",
            "options": {
                "symlink": true
            }
        }
    ],
    
Example: 

    [vendor]/[project] = composer.json package name
    extensions/[vendor]/[project] or extensions/magtools/m2-core     
