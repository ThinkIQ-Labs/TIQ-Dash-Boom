<?php

// lookup the dashboard_php_api script
// we need the file name to make web requests

$dashboard_php_api = new TiqUtilities\Model\Script('tiq_dash_boom_library.dashboard_php_api');
$dashboard_php_api_file_name = $dashboard_php_api->script_file_name;

?>

<script>

    const GetDashboardPhpApiJsonResponseAsync = async (aFunctionName, aArgument) => {

        // typical boiler plate to make a web request to a php script file
        let apiRoute = `/index.php?option=com_thinkiq&task=invokeScript`;
        let settings = { method: 'POST', headers: {} };
        let formData = new FormData();
        formData.append('script_name', '<?php echo $dashboard_php_api_file_name; ?>');
        formData.append('output_type', 'browser');
        formData.append('function', aFunctionName);
        formData.append('argument', JSON.stringify(aArgument));
        settings.body = formData;
        let aResponse = await fetch(apiRoute, settings);
        let aResponseData = await aResponse.json();
        return aResponseData.data;

    };

    var DashboardSdk = {

        EchoAsync: async function(a = null){
            // returns what is put in: string, numbers, json
            let argument={
                hello: a
            };
            return await GetDashboardPhpApiJsonResponseAsync('Echo', { hello : a });
        },

        SaveDashboardAsync: async function(aId, aLayout, aPageTitle, aTabTitle){
            let argument={
                id: aId,
                layout: aLayout,
                pageTitle: aPageTitle,
                tabTitle: aTabTitle
            };
            return await GetDashboardPhpApiJsonResponseAsync('SaveDashboard', argument);
        },

        LoadDashboardAsync: async function(aId){
            let argument={
                id: aId
            };
            return await GetDashboardPhpApiJsonResponseAsync('LoadDashboard', argument);
        }

    }

</script>