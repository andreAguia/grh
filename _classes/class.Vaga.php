<?php
class Vaga{
 /**
  * Abriga as várias rotina do Controle de Vagas de Docentes
  * 
  * @author André Águia (Alat) - alataguia@gmail.com  
  */
    
    
    ###########################################################
    
    /**
    * Método Construtor
    */
    public function __construct(){
        
    }

    ###########################################################
    
    function get_dados($idVaga){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbvaga
                  WHERE idVaga = $idVaga";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################

    /**
     * Método get_nome
     * fornece o nome do servidor e outros dados de uma idServidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_nome($idServidor)
    {
        if(is_numeric($idServidor)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            # Pega os dados
            $nome = $pessoal->get_nome($idServidor);
            $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
            $idSiituacao = $pessoal->get_idSituacao($idServidor);
            $dtSaida = $pessoal->get_dtSaida($idServidor);
            $lotacao = $pessoal->get_lotacaoSimples($idServidor);
            $comissao = $pessoal->get_cargoComissao($idServidor);
            
            if($idSiituacao == 1){
                $css = 'vagasAtivo';
            }else{
                $css = 'vagasInativo';
            }
            
            p($nome,$css);
            p($dtAdmissao."  -  ".$dtSaida,$css);
            p($lotacao,$css);
            if(!vazio($comissao)){
                p("$comissao",$css);
            }
            
        }else{
            return $idServidor;
        }
    }

    ###########################################################

    /**
     * Método get_nomeRel
     * fornece o nome do servidor e outros dados de uma idServidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_nomeRel($idServidor)
    {
        if(is_numeric($idServidor)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            # Pega os dados
            $nome = $pessoal->get_nome($idServidor);
            $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
            $idSiituacao = $pessoal->get_idSituacao($idServidor);
            $dtSaida = $pessoal->get_dtSaida($idServidor);
            $lotacao = $pessoal->get_lotacaoSimples($idServidor);
            $comissao = $pessoal->get_cargoComissao($idServidor);

            if($idSiituacao == 1){
                $nome = "<b>$nome</b>";
            }
            
            echo  $nome;
            br();
            echo $dtAdmissao."  -  ".$dtSaida;
            br();
            echo $lotacao;
            br();
            
            if(!vazio($comissao)){
                echo "$comissao";
            }
        }
    }

    ###########################################################

    /**
     * Método get_servidorOcupante
     * fornece o nome do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_servidorOcupante($idVaga){
        
        if(is_numeric($idVaga)){
            
            # Pega os dados
            $pessoal = new Pessoal();
            
            $select = 'SELECT idServidor,
                              idConcurso,
                              idLotacao,
                              area,
                              tbvagahistorico.obs
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dados = $pessoal->select($select,FALSE);
            
            $idServidor = $dados["idServidor"];
            return $this->get_nome($idServidor);
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_idServidorOcupante
     * fornece o nome do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_idServidorOcupante($idVaga){
            
        # Pega os dados
        $pessoal = new Pessoal();

        $select = 'SELECT idServidor,
                          idConcurso,
                          idLotacao,
                          area,
                          tbvagahistorico.obs
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                    WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';

        $dados = $pessoal->select($select,FALSE);

        $idServidor = $dados["idServidor"];
        return $idServidor;
    }

    ##########################################################

    /**
     * Método get_idServidoresOcupantes
     * fornece oum array com todos os servidores que ocuparam essa vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_idServidoresOcupantes($idVaga){
            
        # Pega os dados
        $pessoal = new Pessoal();

        $select = 'SELECT idServidor
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                    WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital';

        $dados = $pessoal->select($select);
        return $dados;
    }

    ###########################################################

    /**
     * Método get_laboratorioOcupante
     * fornece o nome do laboratório de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_laboratorioOcupante($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idLotacao
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $pessoal->get_nomeLotacao2($dado[0]);
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_concursoOcupante
     * fornece o nome do concurso de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_concursoOcupante($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT concat(anoBase," - Edital: ",DATE_FORMAT(dtPublicacaoEdital,"%d/%m/%Y"))
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_areaOcupante
     * fornece area do concurso de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_areaOcupante($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT area
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_obsOcupante
     * fornece a obs da vaga do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_obsOcupante($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT tbvagahistorico.obs
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_status
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_status($idVaga){
        
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idServidor
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';
            
            $dado = $pessoal->select($select,FALSE);
            
            if(!vazio($dado[0])){
                # Pega a situação
                $idSituacao = $pessoal->get_idSituacao($dado[0]);

                if($idSituacao == 1){
                    return 'Ocupado';
                }else{
                    return 'Disponível';
                }
            }else{
                return 'Disponível';
            }
            
        }else{
            return 'Disponível';
        }
    }

    ###########################################################

    /**
     * Método get_idCargoVaga
     * fornece o idCargo de uma vaga
     * 
     * @param	string $idVaga O id da vaga
     */

    function get_idCargoVaga($idVaga)
    {
        if(is_numeric($idVaga)){
            
            # Conecta o banco
            $pessoal = new Pessoal();
            
            $select = 'SELECT idCargo
                         FROM tbvaga
                        WHERE idVaga = '.$idVaga;
            
            $dado = $pessoal->select($select,FALSE);
            return $dado[0];
            
        }else{
            return "---";
        }
    }

    ###########################################################

    /**
     * Método exibeDadosVaga
     * fornece os dados de uma vaga em forma de tabela
     * 
     * @param	string $idVaga O id da vaga
     */

    function exibeDadosVaga($idVaga){ 
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        $conteudo = $this->get_dados($idVaga);
        
        $painel = new Callout("primary");
        $painel->abre();
        
        $btnEditar = new Link("Editar","areaVagasDocentes.php?fase=editar&id=$idVaga");
        $btnEditar->set_class('button tiny secondary');
        $btnEditar->set_id('editarVaga');
        $btnEditar->set_title('Editar o Procedimento');
        $btnEditar->show();
        
        $centro = $conteudo["centro"];
        $idCargo = $conteudo["idCargo"];
        
        $labOrigem = $servidor->get_nomeLotacao3($this->get_laboratorioOrigem($idVaga));
                
        $cargo = $servidor->get_nomeCargo($idCargo);
        $status = $this->get_status($idVaga);        
                
        p($centro,"vagaCentro");
        p($cargo,"vagaCargo");
        
        $title = "O primeiro laboratório da vaga, para o qual a vaga foi criada,";
        
        p("Laboratório de Origem:","vagaOrigem",NULL,$title);
        p($labOrigem,"vagaCargo",NULL,$title);
        
        $painel->fecha();
        
        $painel = new Callout();
        $painel->abre();
        
        if($status == "Disponível"){
            tituloTable("Ocupante Atual");
            br();
            p("Vaga Disponível","center","f14");
        }else{
            tituloTable("Ocupante Atual");
            br();
            $ocupante = $this->get_servidorOcupante($idVaga);
        }
        
        $painel->fecha();
        
    }

    ###########################################################

    /**
     * Método get_numConcursoVaga
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_numConcursoVaga($idVaga){
            
        # Conecta o banco
        $pessoal = new Pessoal();

        $select = 'SELECT idServidor
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                    WHERE idVaga = '.$idVaga.' ORDER BY tbconcurso.dtPublicacaoEdital desc';

        $dado = $pessoal->count($select);
        return $dado;
    }
    
    ###########################################################

    /**
     * Método get_numVagasCargoDiretoria
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_numVagasDiretoria($dir = NULL){
            
        # Conecta o banco
        $pessoal = new Pessoal();

        $select = "SELECT idVaga
                     FROM tbvaga";
        
        if(!vazio($dir)){
            $select .= " WHERE centro = '$dir'";
        }
        
        $dado = $pessoal->count($select);
        return $dado;
    }
    
    ###########################################################

    /**
     * Método get_numVagasCargoDiretoria
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_numVagasCargoDiretoria($idCargo,$dir = NULL){
            
        # Conecta o banco
        $pessoal = new Pessoal();

        $select = "SELECT idVaga
                     FROM tbvaga
                   WHERE idCargo = $idCargo";
        
        if(!vazio($dir)){
            $select .= " AND centro = '$dir'";
        }

        $dado = $pessoal->count($select);
        return $dado;
    }
    
    ###########################################################


    /**
     * Método get_numVagasCargoDiretoria
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_numVagasCargoDiretoriaDisponiveis($idCargo,$dir = NULL){
            
        # Conecta o banco
        $pessoal = new Pessoal();
        
        # Inicia o contador
        $disponivel = 0;

        # Pega as vagas desse cargo e desse centro
        $select = "SELECT idVaga
                     FROM tbvaga
                    WHERE idCargo = $idCargo";
        
        if(!vazio($dir)){
            $select .= " AND centro = '$dir'";
        }
                      

        $dado = $pessoal->select($select);
        
        # Percorre os resultados
        foreach($dado as $dd){
            $idServidor = $this->get_idServidorOcupante($dd[0]);
            
            # Se não tiver nenhum candidato
            if(is_null($idServidor)){
                $disponivel++;
            }else{
                $situacao = $pessoal->get_situacao($idServidor);                
                
                # Compara de é inativo
                if($situacao <> "Ativo"){
                    $disponivel++;
                }
            }
        }
        return $disponivel;
    }
    
    ###########################################################

    /**
     * Método get_numVagasCargoDiretoria
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function get_numVagasCargoDiretoriaOcupados($idCargo,$dir = NULL){
            
        # Conecta o banco
        $pessoal = new Pessoal();
        
        # Inicia o contador
        $ocupado = 0;

        # Pega as vagas desse cargo e desse centro
        $select = "SELECT idVaga
                     FROM tbvaga
                    WHERE idCargo = $idCargo";
        
        if(!vazio($dir)){
            $select .= " AND centro = '$dir'";
        }

        $dado = $pessoal->select($select);
        
        # Percorre os resultados
        foreach($dado as $dd){
            $idServidor = $this->get_idServidorOcupante($dd[0]);
            
            # Se não tiver nenhum candidato
            if(!is_null($idServidor)){
                $situacao = $pessoal->get_situacao($idServidor);
                
                # Compara se está ocupado
                if($situacao == "Ativo"){
                    $ocupado++;
                }
            }
        }
        return $ocupado;
    }
    
    ###########################################################
    /**
     * Método exibeTotalVagas
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function exibeTotalVagas($centro = NULL,$tipo = NULL){
        
        # Conecta o banco
        $pessoal = new Pessoal();

        # Pega as diretorias
        if(vazio($centro)){
            $diretorias = array("CCT","CCTA","CCH","CBB");
        }else{
            $diretorias[] = $centro;
        }
        
            # Pega os Cargos
            $cargos = array(128,129);
            $numeroCargos = count($cargos);

            # Cria um array onde terá os resultados
            $resultado = array();

            # Cria e preenche o array do total da coluna
            $totalColuna = array();
            $totalColuna = array_fill(0, $numeroCargos+2, 0);

            $totalColunaDisponivel = array();
            $totalColunaDisponivel = array_fill(0, $numeroCargos+2, 0);

            $totalColunaOcupado = array();
            $totalColunaOcupado = array_fill(0, $numeroCargos+2, 0);

            # Cria e preenche o array do label
            $label = array("Centro");
            foreach($cargos as $cc){
                $label[] = $pessoal->get_nomeCargo($cc);
            }
            $label[] = "Total";

            # Zera o contador de linha
            $linha = 0;
            
            # Percorre as diretorias
            foreach($diretorias as $dd){

                $resultado[$linha][0] = $dd;    // Sigla da Diretoria 
                $coluna = 1;                    // Inicia a coluna

                # Zera os totais de linha
                $totalLinha = 0;                // Zera totalizador de cada linha
                $totalLinhaDisponivel = 0;                // Zera totalizador de cada linha
                $totalLinhaOcupado = 0;                // Zera totalizador de cada linha

                # Percorre as colunas / Cargos
                foreach($cargos as $cc){
                    # Faz os cálculos
                    $quantidade = $this->get_numVagasCargoDiretoria($cc, $dd);
                    $disponivel = $this->get_numVagasCargoDiretoriaDisponiveis($cc, $dd);
                    $ocupado = $this->get_numVagasCargoDiretoriaOcupados($cc, $dd);

                    # Joga os Valores no array
                    #$resultado[$linha][$coluna] = "$quantidade ( <span id='verde'>$disponivel</span> / <span id='vermelho'>$ocupado</span> )";
                    
                    switch ($tipo){                        
                        case "o":
                            $resultado[$linha][$coluna] = $ocupado;
                            break;
                        case "d" :
                            $resultado[$linha][$coluna] = $disponivel;
                            break;
                        default :
                            $resultado[$linha][$coluna] = $quantidade;
                            break;
                    }

                    # Somatorio da linha
                    $totalLinha += $quantidade;
                    $totalLinhaDisponivel += $disponivel;
                    $totalLinhaOcupado += $ocupado;

                    # Somatório da coluna
                    $totalColuna[$coluna] += $quantidade;
                    $totalColunaDisponivel[$coluna] += $disponivel;
                    $totalColunaOcupado[$coluna] += $ocupado;

                    # Incrementa a coluna
                    $coluna++;
                }

                # Faz a última coluna com o total da linha
                #$resultado[$linha][$coluna] = "$totalLinha ( <span id='verde'>$totalLinhaDisponivel</span> / <span id='vermelho'>$totalLinhaOcupado</span> )";
                
                switch ($tipo){                        
                    case "o":
                        $resultado[$linha][$coluna] = $totalLinhaOcupado;
                        break;
                    case "d" :
                        $resultado[$linha][$coluna] = $totalLinhaDisponivel;
                        break;
                    default :
                        $resultado[$linha][$coluna] = $totalLinha;
                        break;
                }


                # Somatório da coluna
                $totalColuna[$coluna] += $totalLinha;
                $totalColunaDisponivel[$coluna] += $totalLinhaDisponivel;
                $totalColunaOcupado[$coluna] += $totalLinhaOcupado;

                # Incrementa a linha
                $linha++;


            # Faz a última lina com os totais das colunas
            $resultado[$linha][0] = "Total";
            $coluna = 1;

            # Percorre as colunas
            foreach($cargos as $cc){
                #$resultado[$linha][$coluna] = "$totalColuna[$coluna] ( <span id='verde'>$totalColunaDisponivel[$coluna]</span> / <span id='vermelho'>$totalColunaOcupado[$coluna]</span> )";
                
                switch ($tipo){                        
                    case "o":
                        $resultado[$linha][$coluna] = $totalColunaOcupado[$coluna];
                        break;
                    case "d" :
                        $resultado[$linha][$coluna] = $totalColunaDisponivel[$coluna];
                        break;
                    default :
                        $resultado[$linha][$coluna] = $totalColuna[$coluna];
                        break;
                }
                $coluna++;
            }
        }
        #$resultado[$linha][$coluna] = "$totalColuna[$coluna] ( <span id='verde'>$totalColunaDisponivel[$coluna]</span> / <span id='vermelho'>$totalColunaOcupado[$coluna]</span> )";

        switch ($tipo){                        
            case "o":
                $resultado[$linha][$coluna] = $totalColunaOcupado[$coluna];
                $titulo = "Vagas Ocupadas";
                break;
            case "d" :
                $resultado[$linha][$coluna] = $totalColunaDisponivel[$coluna];
                $titulo = "Vagas Disponíveis";
                break;
            default :
                $resultado[$linha][$coluna] = $totalColuna[$coluna];
                $titulo = "Vagas Totais";
                break;
        }
            
        $tabela = new Tabela();
        $tabela->set_titulo($titulo);
        $tabela->set_conteudo($resultado);
        $tabela->set_label($label);
        $tabela->set_width(array(25,25,25,25));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("left","center"));       
        $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                            'valor' => "Total",
                                            'operador' => '=',
                                            'id' => 'totalVagas')));
        $tabela->show();
    }
    
    ###########################################################

    /**
     * Método get_idConcursoProfessor
     * fornece o idConcurso de um professor
     * 
     * @param	string $idServidor O $idServidor do servidor Professor
     */

    function get_idConcursoProfessor($idServidor){
            
        # Conecta o banco
        $pessoal = new Pessoal();

        $select = 'SELECT idConcurso
                     FROM tbvagahistorico
                    WHERE idServidor = '.$idServidor;

        $dado = $pessoal->select($select,FALSE);
        return $dado[0];
    }

    ###########################################################

    /**
     * Método get_idConcursoProfessor
     * fornece o idConcurso de um professor
     * 
     * @param	string $idServidor O $idServidor do servidor Professor
     */

    function exibeGrafico($idvaga){
            
        # Conecta o banco
        $pessoal = new Pessoal();
        
        # Pega todos os concursos dessa vaga
        $select = 'SELECT *
                     FROM tbvagahistorico
                    WHERE idVaga = '.$idvaga;

        $dado = $pessoal->select($select);
        
        # Classes
        $concurso = new Concurso();
        
        # Inicia os arrays da tabela
        $label = array();
        $conteudo = array();
        
        
        # Percorre a tabela
        foreach($dados as $dd){
            
            $label[] = $concurso->get_nomeConcurso($dd["idConcurso"]);
            $laboratorio[] = $pessoal->get_nomeLotacao($dd["idiLotacao"]);
            $area[] = $dd["area"];
            
            # Parei aqui
        }
        
        $tabela = new Tabela();
        $tabela->set_titulo("Tabela Simples");
        $tabela->set_conteudo($laboratorio);
        $tabela->set_label($label);
        $tabela->set_align(array("center"));
        $tabela->show();
    }

    ###########################################################
    /**
     * Método exibeVagasOcupadas
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function exibeVagasOcupadas($centro = NULL){
        
        # Conecta o banco
        $pessoal = new Pessoal();
        
        # Inicia as variáveis
        $resultado = array();
        
        # Faz os cálculos
        $ocupadoTitular = $this->get_numVagasCargoDiretoriaOcupados(129, $centro);
        $ocupadoAssociado = $this->get_numVagasCargoDiretoriaOcupados(128, $centro);
        
        $resultado[] = array("Professor Titular",$ocupadoTitular);
        $resultado[] = array("Professor Associado",$ocupadoAssociado);
        $resultado[] = array("Total",$ocupadoAssociado + $ocupadoTitular);
            
        $titulo = "Vagas Ocupadas";
        
        if(!vazio($centro)){
            $titulo .= " do $centro";
        }
            
        $tabela = new Tabela();
        $tabela->set_titulo($titulo);
        $tabela->set_conteudo($resultado);
        $tabela->set_label(array("Cargo","Quantidade"));
        #$tabela->set_width(array());
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("left","center"));       
        $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                            'valor' => "Total",
                                            'operador' => '=',
                                            'id' => 'totalVagas')));
        $tabela->show();
    }
    
    ###########################################################
    /**
     * Método exibeVagasOcupadas
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */

    function exibeVagasDisponiveis($centro = NULL){
        
        # Conecta o banco
        $pessoal = new Pessoal();
        
        # Inicia as variáveis
        $resultado = array();
        
        # Faz os cálculos
        $ocupadoTitular = $this->get_numVagasCargoDiretoriaDisponiveis(129, $centro);
        $ocupadoAssociado = $this->get_numVagasCargoDiretoriaDisponiveis(128, $centro);
        
        $resultado[] = array("Professor Titular",$ocupadoTitular);
        $resultado[] = array("Professor Associado",$ocupadoAssociado);
        $resultado[] = array("Total",$ocupadoAssociado + $ocupadoTitular);
        
        $titulo = "Vagas Disponíveis";
        
        if(!vazio($centro)){
            $titulo .= " do $centro";
        }
            
        $tabela = new Tabela();
        $tabela->set_titulo($titulo);
        $tabela->set_conteudo($resultado);
        $tabela->set_label(array("Cargo","Quantidade"));
        #$tabela->set_width(array());
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("left","center"));       
        $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                            'valor' => "Total",
                                            'operador' => '=',
                                            'id' => 'totalVagas')));
        $tabela->show();
    }
    
    ###########################################################

    /**
     * Método get_laboratorioOrigem
     * fornece o primeiro laboratório de uma vaga. o laborató ao qual a vaga foi criada,
     * 
     * @param	integer $idVaga O id da vaga
     */

    function get_laboratorioOrigem($idVaga){
            
        # Conecta o banco
        $pessoal = new Pessoal();

        $select = "SELECT idLotacao
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso) 
                    WHERE idVaga = $idVaga
                 ORDER BY tbconcurso.dtPublicacaoEdital LIMIT 1";

        $dado = $pessoal->select($select,FALSE);
        return $dado[0];
    }

    ###########################################################
    
}