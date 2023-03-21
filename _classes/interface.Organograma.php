<?php

class Organograma {

    /**
     * Monta um Organograma
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $topo string null A lotação que será o topo do organograma (diretoria ou a reitoria)
     */
    private $topo = null;

###########################################################

    public function __construct($topo = null) {

        /**
         * Inicia a classe
         * 
         * @param $topo  string null A lotação que será o topo do organograma (diretoria ou a reitoria)
         * 
         * @syntax $button = new Chart($tipo,$dados);
         */
        $this->topo = $topo;
    }

###########################################################

    public function show() {
        /**
         * Exibe o gráfico
         * 
         * @syntax $button->show();
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $lotacaoClasse = new Lotacao();

        # Verifica qual é o orgenograma
        switch ($this->topo) {
            case "Pró Reitorias" :
                $lotacao = $pessoal->select("SELECT idLotacao, DIR 
                                       FROM tblotacao
                                      WHERE ativo
                                        AND substring(DIR,1,3) = 'PRO'
                                        AND GER <> 'Afastados'
                                        AND GER <> 'Cedidos'                                        
                                   ORDER BY DIR, GER");

                # Carrega as rotinas do Google
                echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>";

                # Cria a função
                echo "<script type='text/javascript'>
                google.charts.load('current', {packages:['orgchart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                  var data = new google.visualization.DataTable();
                  data.addColumn('string', 'Name');
                  data.addColumn('string', 'Manager');
                  data.addColumn('string', 'ToolTip');

                  // For each orgchart box, provide the name, manager, and tooltip to show.
                  data.addRows([";

                #########
                # Exibe a sigla da Lotação ancora
                echo "[{'v':'{$this->topo}','f':'<div id=\"siglaOrga\">Pró Reitorias</div>";

                # Exibe o Nome da Diretoria
                echo "<div id=\"nomeOrga\">{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}</div>";

                # Exibe o cargo em comissão
                $idServidor = $lotacaoClasse->get_diretorSigla($this->topo);
                if (!empty($idServidor) AND $idServidor <> "---") {
                    echo "<div id=\"servidorOrga\">{$pessoal->get_nome($idServidor)}</div>";
                    echo "<div id=\"servidorCargo\">{$pessoal->get_cargoComissao($idServidor)}</div>";
                }

                # Exibe o Setor acima, ou seja nenhum, e o Nome da Diretoria no title
                echo "'},'','{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}'],";

                # Percorre as lotações
                foreach ($lotacao as $item) {
                    $idServidor = $pessoal->get_diretor($item['idLotacao']);
                    $nomeDir = $lotacaoClasse->get_nomeDiretoria($item['idLotacao']);

                    # Exibe a sigla da lotação 
                    echo "['{$item['DIR']}";

                    # Exibe o nome da lotação
                    echo "<div style=\"color:red; font-size:12px;\">{$nomeDir}</div>";

                    # Exibe o cargo em comissão
                    if (!empty($idServidor) AND $idServidor <> "---") {
                        echo "<div id=\"servidorOrga\">{$pessoal->get_nome($idServidor)}</div>";
                        echo "<div id=\"servidorCargo\">{$pessoal->get_cargoComissao($idServidor)}</div>";
                    }

                    # Informa o setor hierarquicamente acima e o title
                    echo "','{$this->topo}','{$nomeDir}'],";
                }

                echo "]);";

                echo "        // Create the chart.
                      var chart = new google.visualization.OrgChart(document.getElementById('organograma2'));
                      // Draw the chart, setting the allowHtml option to true for the tooltips.
                      chart.draw(data, {'allowHtml':true});
                    }
             </script>";

                # Abre a div do organograma
                echo "<div id='organograma2'></div>";
                break;

            ################################################################
            case "Centros" :
                # Pega as diretorias
                $lotacao = $pessoal->select("SELECT idLotacao, DIR 
                                       FROM tblotacao
                                      WHERE ativo
                                        AND substring(DIR,1,1) = 'C'
                                        AND GER <> 'Afastados'
                                        AND GER <> 'Cedidos'                                        
                                   ORDER BY DIR, GER");

                # Carrega as rotinas do Google
                echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>";

                # Cria a função
                echo "<script type='text/javascript'>
                google.charts.load('current', {packages:['orgchart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                  var data = new google.visualization.DataTable();
                  data.addColumn('string', 'Name');
                  data.addColumn('string', 'Manager');
                  data.addColumn('string', 'ToolTip');

                  // For each orgchart box, provide the name, manager, and tooltip to show.
                  data.addRows([";

                #########
                # Exibe a sigla da Lotação ancora
                echo "[{'v':'{$this->topo}','f':'<div id=\"siglaOrga\">Acadêmico</div>";

                # Exibe o Nome da Diretoria
                echo "<div id=\"nomeOrga\">{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}</div>";

                # Exibe o cargo em comissão
                $idServidor = $lotacaoClasse->get_diretorSigla($this->topo);
                if (!empty($idServidor) AND $idServidor <> "---") {
                    echo "<div id=\"servidorOrga\">{$pessoal->get_nome($idServidor)}</div>";
                    echo "<div id=\"servidorCargo\">{$pessoal->get_cargoComissao($idServidor)}</div>";
                }

                # Exibe o Setor acima, ou seja nenhum, e o Nome da Diretoria no title
                echo "'},'','{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}'],";

                # Percorre as lotações
                foreach ($lotacao as $item) {
                    $idServidor = $pessoal->get_diretor($item['idLotacao']);
                    $nomeDir = $lotacaoClasse->get_nomeDiretoria($item['idLotacao']);

                    # Exibe a sigla da lotação 
                    echo "['{$item['DIR']}";

                    # Exibe o nome da lotação
                    echo "<div style=\"color:red; font-size:12px;\">{$nomeDir}</div>";

                    # Exibe o cargo em comissão
                    if (!empty($idServidor) AND $idServidor <> "---") {
                        echo "<div id=\"servidorOrga\">{$pessoal->get_nome($idServidor)}</div>";
                        echo "<div id=\"servidorCargo\">{$pessoal->get_cargoComissao($idServidor)}</div>";
                    }

                    # Informa o setor hierarquicamente acima e o title
                    echo "','{$this->topo}','{$nomeDir}'],";
                }

                echo "]);";

                echo "        // Create the chart.
                      var chart = new google.visualization.OrgChart(document.getElementById('organograma3'));
                      // Draw the chart, setting the allowHtml option to true for the tooltips.
                      chart.draw(data, {'allowHtml':true});
                    }
             </script>";

                # Abre a div do organograma
                echo "<div id='organograma3'></div>";
                break;

            ################################################################
            case "Administrativo" :
                # Pega as diretorias
                $lotacao = $pessoal->select("SELECT idLotacao, DIR 
                                       FROM tblotacao
                                      WHERE ativo
                                        AND substring(DIR,1,1) <> 'C'
                                        AND substring(DIR,1,3) <> 'PRO'
                                        AND DIR <> 'Reitoria'
                                        AND GER <> 'Afastados'
                                        AND GER <> 'Cedidos'                                        
                                   ORDER BY DIR, GER");

                # Carrega as rotinas do Google
                echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>";

                # Cria a função
                echo "<script type='text/javascript'>
                google.charts.load('current', {packages:['orgchart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                  var data = new google.visualization.DataTable();
                  data.addColumn('string', 'Name');
                  data.addColumn('string', 'Manager');
                  data.addColumn('string', 'ToolTip');

                  // For each orgchart box, provide the name, manager, and tooltip to show.
                  data.addRows([";

                #########
                # Exibe a sigla da Lotação ancora
                echo "[{'v':'{$this->topo}','f':'<div id=\"siglaOrga\">Administrativo</div>";

                # Exibe o Nome da Diretoria
                echo "<div id=\"nomeOrga\">{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}</div>";

                # Exibe o cargo em comissão
                $idServidor = $lotacaoClasse->get_diretorSigla($this->topo);
                if (!empty($idServidor) AND $idServidor <> "---") {
                    echo "<div id=\"servidorOrga\">{$pessoal->get_nome($idServidor)}</div>";
                    echo "<div id=\"servidorCargo\">{$pessoal->get_cargoComissao($idServidor)}</div>";
                }

                # Exibe o Setor acima, ou seja nenhum, e o Nome da Diretoria no title
                echo "'},'','{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}'],";

                # Percorre as lotações
                foreach ($lotacao as $item) {
                    $idServidor = $pessoal->get_diretor($item['idLotacao']);
                    $nomeDir = $lotacaoClasse->get_nomeDiretoria($item['idLotacao']);

                    # Exibe a sigla da lotação 
                    echo "['{$item['DIR']}";

                    # Exibe o nome da lotação
                    echo "<div style=\"color:red; font-size:12px;\">{$nomeDir}</div>";

                    # Exibe o cargo em comissão
                    if (!empty($idServidor) AND $idServidor <> "---") {
                        echo "<div id=\"servidorOrga\">{$pessoal->get_nome($idServidor)}</div>";
                        echo "<div id=\"servidorCargo\">{$pessoal->get_cargoComissao($idServidor)}</div>";
                    }

                    # Informa o setor hierarquicamente acima e o title
                    echo "','{$this->topo}','{$nomeDir}'],";
                }

                echo "]);";

                echo "        // Create the chart.
                      var chart = new google.visualization.OrgChart(document.getElementById('organograma4'));
                      // Draw the chart, setting the allowHtml option to true for the tooltips.
                      chart.draw(data, {'allowHtml':true});
                    }
             </script>";

                # Abre a div do organograma
                echo "<div id='organograma4'></div>";
                break;
            ################################################################
            default:

                # Pega as diretorias
                $lotacao = $pessoal->select("SELECT idLotacao, DIR, GER, nome
                                       FROM tblotacao
                                      WHERE ativo
                                        AND DIR = '{$this->topo}'
                                        AND GER <> 'Afastados'
                                        AND GER <> 'Cedidos'
                                        AND GER <> 'CCM'
                                        AND GER <> 'CGAB'
                                        AND GER <> 'Rio'
                                        AND GER <> 'SECR'
                                   ORDER BY DIR, GER");

                # Carrega as rotinas do Google
                echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>";

                # Cria a função
                echo "<script type='text/javascript'>
                google.charts.load('current', {packages:['orgchart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                  var data = new google.visualization.DataTable();
                  data.addColumn('string', 'Name');
                  data.addColumn('string', 'Manager');
                  data.addColumn('string', 'ToolTip');

                  // For each orgchart box, provide the name, manager, and tooltip to show.
                  data.addRows([";

                #########
                # Exibe a sigla da Lotação ancora
                echo "[{'v':'{$this->topo}','f':'<div id=\"siglaOrga\">{$this->topo}</div>";

                # Exibe o Nome da Diretoria
                echo "<div id=\"nomeOrga\">{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}</div>";

                # Exibe o cargo em comissão
                $idServidor = $lotacaoClasse->get_diretorSigla($this->topo);
                if (!empty($idServidor) AND $idServidor <> "---") {
                    echo "<div id=\"servidorOrga\">{$pessoal->get_nome($idServidor)}</div>";
                    echo "<div id=\"servidorCargo\">{$pessoal->get_cargoComissao($idServidor)}</div>";
                }

                # Exibe o Setor acima, ou seja nenhum, e o Nome da Diretoria no title
                echo "'},'','{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}'],";

                # Percorre as lotações
                foreach ($lotacao as $item) {
                    $idServidor = $pessoal->get_gerente($item['idLotacao']);

                    # Exibe a sigla da lotação 
                    echo "['{$item['GER']}";

                    # Exibe o nome da lotação
                    echo "<div id=\"nomeOrga\">{$item['nome']}</div>";

                    # Exibe o cargo em comissão
                    if (!empty($idServidor) AND $idServidor <> "---") {
                        echo "<div id=\"servidorOrga\">{$pessoal->get_nome($idServidor)}</div>";
                        echo "<div id=\"servidorCargo\">{$pessoal->get_cargoComissao($idServidor)}</div>";
                    }

                    # Informa o setor hierarquicamente acima e o title
                    echo "','{$item['DIR']}','{$item['nome']}'],";
                }


                echo "]);";

                echo "        // Create the chart.
                      var chart = new google.visualization.OrgChart(document.getElementById('organograma1'));
                      // Draw the chart, setting the allowHtml option to true for the tooltips.
                      chart.draw(data, {'allowHtml':true});
                    }
             </script>";

                # Abre a div do organograma
                echo "<div id='organograma1'></div>";
                break;
        }
    }

}
