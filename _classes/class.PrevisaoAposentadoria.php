<?php

class PrevisaoAposentadoria {

    /**
     * Abriga a classe de Previsão de APosentadoria
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    /*
     *  Descricao
     */
    private $tipo = null;           // O Tipo da regra de aposentadoria
    private $descricao = null;      // A descrição da da regra de aposentadoria
    private $descricaoResumida1 = null;
    private $descricaoResumida2 = null;
    private $legislacao = null;     // A lei 

    /*
     *  Datas Especiais
     */
    private $dtIngresso = null;             // Algumas regras exige até uma data de ingresso
    private $dtIngressoApartir = null;      // Algumas regras exige a partir de uma data de ingresso
    private $dtRequesitosCumpridos = null;  // Algumas regras exigem prazo para os requisitos seja cumpridos

    /*
     *  Regras Gerais    
     */
    private $idadeHomem = null;
    private $idadeMulher = null;
    private $contribuicaoHomem = null;
    private $contribuicaoMulher = null;
    private $servicoPublico = null;
    private $cargoEfetivo = null;
    private $carreira = null;
    private $permiteAbonoPermanencia = true;

    /*
     *  Regras tratadas
     */
    private $regraIdade = null;
    private $regraContribuicao = null;

    /*
     *  Pontuação
     */
    private $pontosHomem = null;
    private $pontosMulher = null;
    private $tabelaM = null;
    private $tabelaF = null;

    /*
     * Pedágio
     */
    private $pedagio = null;
    private $pedagioData = null;

    /*
     * Redutor    
     */
    private $temRedutor = false;
    private $tempoExcedente = null;
    private $diasIdadeQueFalta = null;
    private $mesesIdadeQueFalta = null;
    private $diasParaPagar = null;
    private $mesesParaPagar = null;
    private $mensagemRedutor = null;
    private $dataIdadeAposContribuicao = null;

    /*
     *  Regras específica para restrição de idade    
     */
    private $dataDivisorIdade = null;
    private $idadeHomemAntes = null;
    private $idadeHomemDepois = null;
    private $idadeMulherAntes = null;
    private $idadeMulherDepois = null;

    /*
     *  Remuneração
     */
    private $calculoInicial = null;
    private $percentualDevido = null;
    private $teto = null;
    private $reajuste = null;
    private $paridade = null;

    /*
     *  Data da Lei - Só pode aposentar apos essa data
     */
    private $dataLei = null;
    private $ajustado = false;

    /*
     *  Cartilhas
     */
    private $cartilha1 = null;
    private $cartilha2 = null;

    /*
     *  Descrições
     */
    private $dtIngressoDescricao = "Data de ingresso no serviço público sem interrupção.";
    private $dtIngressoApartirDescricao = "Servidor tem que ingressar no serviço público a partir desta data.";
    private $tempoContribuiçãoDescricao = "Tempo Total averbado<br/>(público e privado).";
    private $idadeDescricao = "Idade do servidor.";
    private $tempoPublicoDescicao = "Tempo trabalhado de todos os periodo públicos.";
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";
    private $pontuacaoInicialDescricao = "Pontuação Inicial.";
    private $pedagioDescricao = "Período adicional de contribuição calculado apartir do tempo de contribuição que faltava ao servidor em ";
    private $compulsoriaDescricao = "A data calculada deve ser anterior a data da aposentadoria compulsória";
    private $carreiraDescricao = "Tempo dentro da Uenf. (No mesmo Órgão)";

    /*
     *  Relatório
     */
    private $mensagemRelatorio = "Atenção, esta é uma previsão da posentadoria"
            . " e as informações aqui contidas podem variar com o tempo.<br/>"
            . "As regras de aposentadoria estão diponíveis no site da GRH. https://uenf.br/dga/grh/";

    /*
     *  Dados do servidor
     */
    private $idServidor = null;
    private $servidorIdade = null;
    private $servidorSexo = null;
    private $servidorDataIngresso = null;
    private $servidorPontos = null;
    private $servidorDataNascimento = null;

    # Do pedágio
    private $servidorTempoAntesData = null;
    private $servidorTempoSobra = null;
    private $servidorPedagio = null;

    # Tempo do Servidor
    private $servidorTempoAverbadoPublico = null;
    private $servidorTempoAverbadoPrivado = null;
    private $servidorTempoUenf = null;
    private $servidorTempoPublicoIninterrupto = null;
    private $servidorTempoPublico = null;
    private $servidorTempoContribuicao = null;
    private $servidorTempoContribuicaoDescontado = null;

    # Analises
    private $analisaDtIngresso = null;
    private $analisaDtIngressoApartir = null;
    private $analiseIdade = null;
    private $analiseContribuicao = null;
    private $analisePublico = null;
    private $analiseCargoEfetivo = null;
    private $analiseDtRequesitosCumpridos = null;
    private $analisePontos = null;
    private $analisePedagio = null;
    private $analiseCompulsoria = null;
    private $analiseReducao = null;
    private $analiseCarreira = null;

    # Obs
    private $obsContribuicao = null;
    private $obsCarreira = null;
    private $obsServicoPublico = null;
    private $obsTempoCargo = null;
    private $dtIngressoObs = null;

    /*
     *  Variaveis de Retorno    
     */
    private $dataCriterioIngresso = null;
    private $dataCriterioIdade = null;
    private $dataCriterioPontos = null;
    private $dataCriterioPedagio = null;
    private $dataCriterioTempoContribuicao = null;
    private $dataCriterioTempoContribuicaoOriginal = null;
    private $dataCriterioTempoServicoPublico = null;
    private $dataCriterioTempoServicoPublicoOriginal = null;
    private $dataCriterioTempoCargo = null;
    private $dataDireitoAposentadoria = null;
    private $dataCriterioRedutor = null;
    private $dataCriterioCarreira = null;
    private $dataCriterioCarreiraOriginal = null;
    private $temDireito = true;
    private $textoRetorno = null;
    private $textoReduzido = null;
    private $corFundo = null;

    # Aposentadoria Compulsoria
    private $dataCompulsoria = null;

    # Verifica se compara com a compulsória
    private $verificaCompulsoria = true;

    # Modalidade
    private $modalidade;

    # Afastamentos Especificos
    private $afastementosEspecificos = true;

    # Mensagem na tabela de dados
    private $mensagem = null;

    ###########################################################

    public function __construct($modalidade = null, $idServidor = null) {

        # Preenche as variáveis de acordo com o tipo de aposentadoria
        switch ($modalidade) {

            ######################################

            case "permanente1" :
            case "voluntaria" :

                # Descrição
                $this->tipo = "Regra Permanente";
                $this->descricao = "Aposentadoria Voluntária por Idade e Tempo de Contribuição";
                $this->descricaoResumida1 = "Aposentadoria Voluntária";
                $this->legislacao = "Art. 2º, inciso III, da Lei Complementar nº 195/2021";

                # Regras
                $this->idadeHomem = 65;
                $this->idadeMulher = 62;
                $this->contribuicaoHomem = 25;
                $this->contribuicaoMulher = 25;
                $this->servicoPublico = 10;
                $this->cargoEfetivo = 5;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Remuneração
                $this->calculoInicial = "Média aritmética simples de TODAS as remunerações a partir de julho de 1994 - Lei Federal 10.887";
                $this->percentualDevido = "60% + 2% para cada ano que exceder 20 anos de contribuição";
                $this->reajuste = "INPC – Lei 6.244/2012";
                $this->paridade = "SEM PARIDADE";

                # Data da Lei - Só pode aposentar apos essa data
                $this->dataLei = "01/01/2022";

                # Cartilha
                $this->cartilha1 = "lc195voluntaria1.png";
                $this->cartilha2 = "lc195voluntaria2.png";
                break;

            ######################################

            case "permanente2" :
            case "compulsoria" :

                # Descrição
                $this->tipo = "Regra Permanente";
                $this->descricao = "Aposentadoria Compulsória por Idade";
                $this->descricaoResumida1 = "Aposentadoria Compulsória";
                $this->legislacao = "Art. 2º, inciso II, da Lei Complementar nº 195/2021";

                # Regras
                $this->idadeHomem = 75;
                $this->idadeMulher = 75;
                $this->contribuicaoHomem = null;
                $this->contribuicaoMulher = null;
                $this->servicoPublico = null;
                $this->cargoEfetivo = null;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Remuneração
                $this->calculoInicial = "Média aritmética simples de TODAS as remunerações a partir de julho de 1994 - Lei Federal 10.887";
                $this->percentualDevido = "60% + 2% para cada ano que exceder 20 anos de contribuição";
                $this->reajuste = "INPC – Lei 6.244/2012";
                $this->paridade = "SEM PARIDADE";

                # Retira a verificação da compulsória
                $this->verificaCompulsoria = false;

                # Cartilha
                $this->cartilha1 = "lc195compulsoria1.png";
                $this->cartilha2 = "lc195compulsoria2.png";

                # Afastamentos Especícicos
                $this->afastementosEspecificos = false;
                break;

            ######################################3

            case "pontos1" :

                # Descrição
                $this->tipo = "Regra de Transição";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra dos Pontos - Integralidade e Paridade";
                $this->descricaoResumida1 = "Pontos";
                $this->descricaoResumida2 = "Integralidade e Paridade";
                $this->legislacao = "Artigo 3º da EC nº 90/2021";

                # Regras
                $this->idadeHomem = 65;
                $this->idadeMulher = 62;
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 20;
                $this->cargoEfetivo = 5;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Datas
                $this->dtIngresso = "31/12/2003";
                $this->dataLei = "01/01/2022";

                # Remuneração
                $this->calculoInicial = "Última remuneração";
                $this->teto = "Remuneração do servidor no cargo efetivo";
                $this->reajuste = "Na mesma data e índice dos servidores ativos";
                $this->paridade = "COM PARIDADE";

                # Cartilha
                $this->cartilha1 = "transicaoPontos11.png";
                $this->cartilha2 = "transicaoPontos12.png";

                # Pontuação
                $this->pontosHomem = 96;
                $this->pontosMulher = 86;

                # Tabela de Pontos
                $this->tabelaM = [
                    [2023, 97],
                    [2024, 97],
                    [2025, 98],
                    [2026, 98],
                    [2027, 99],
                    [2028, 99],
                    [2029, 100],
                    [2030, 100],
                    [2031, 101],
                    [2032, 101],
                    [2033, 102],
                    [2034, 102],
                    [2035, 103],
                    [2036, 103],
                    [2037, 104],
                    [2038, 104],
                    [2039, 105],
                    [2040, 105],
                ];
                $this->tabelaF = [
                    [2023, 87],
                    [2024, 87],
                    [2025, 88],
                    [2026, 88],
                    [2027, 89],
                    [2028, 89],
                    [2029, 90],
                    [2030, 90],
                    [2031, 91],
                    [2032, 91],
                    [2033, 92],
                    [2034, 92],
                    [2035, 93],
                    [2036, 93],
                    [2037, 94],
                    [2038, 94],
                    [2039, 95],
                    [2040, 95],
                    [2041, 96],
                    [2042, 96],
                    [2043, 97],
                    [2044, 97],
                    [2045, 98],
                    [2046, 98],
                    [2047, 99],
                    [2048, 99],
                ];
                break;

            ######################################3

            case "pontos2" :

                # Descrição
                $this->tipo = "Regra de Transição";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra dos Pontos - Média";
                $this->descricaoResumida1 = "Pontos";
                $this->descricaoResumida2 = "Média";
                $this->legislacao = "Artigo 3º da EC nº 90/2021";

                # Regras
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 20;
                $this->cargoEfetivo = 5;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Datas
                $this->dtIngresso = "31/12/2021";
                $this->dataLei = "01/01/2022";

                # Regra específica da idade
                $this->dataDivisorIdade = "01/01/2025";
                $this->idadeHomemAntes = 61;
                $this->idadeHomemDepois = 62;
                $this->idadeMulherAntes = 56;
                $this->idadeMulherDepois = 57;

                # Remuneração
                $this->calculoInicial = "Média aritmética simples das 80% maiores remunerações a partir de julho de 1994";
                $this->teto = "Remuneração do servidor no cargo efetivo";
                $this->reajuste = "INPC - Lei 6.2442012";
                $this->paridade = "SEM PARIDADE";

                # Cartilha
                $this->cartilha1 = "transicaoPontos21.png";
                $this->cartilha2 = "transicaoPontos22.png";

                # Pontuação
                $this->pontosHomem = 90;
                $this->pontosMulher = 86;

                # Tabela de Pontos
                $this->tabelaM = [
                    [2023, 97],
                    [2024, 97],
                    [2025, 98],
                    [2026, 98],
                    [2027, 99],
                    [2028, 99],
                    [2029, 100],
                    [2030, 100],
                    [2031, 101],
                    [2032, 101],
                    [2033, 102],
                    [2034, 102],
                    [2035, 103],
                    [2036, 103],
                    [2037, 104],
                    [2038, 104],
                    [2039, 105],
                    [2040, 105],
                ];

                $this->tabelaF = [
                    [2023, 87],
                    [2024, 87],
                    [2025, 88],
                    [2026, 88],
                    [2027, 89],
                    [2028, 89],
                    [2029, 90],
                    [2030, 90],
                    [2031, 91],
                    [2032, 91],
                    [2033, 92],
                    [2034, 92],
                    [2035, 93],
                    [2036, 93],
                    [2037, 94],
                    [2038, 94],
                    [2039, 95],
                    [2040, 95],
                    [2041, 96],
                    [2042, 96],
                    [2043, 97],
                    [2044, 97],
                    [2045, 98],
                    [2046, 98],
                    [2047, 99],
                    [2048, 99],
                ];
                break;

            ######################################3

            case "pedagio1" :

                # Descrição
                $this->tipo = "Regra de Transição";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra do Pedágio - Integralidade e Paridade";
                $this->descricaoResumida1 = "Pedágio";
                $this->descricaoResumida2 = "Integralidade e Paridade";
                $this->legislacao = "Artigo 4º da EC nº 90/2021";

                # Regras
                $this->idadeHomem = 60;
                $this->idadeMulher = 55;
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 20;
                $this->cargoEfetivo = 5;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Pedagio
                $this->pedagio = 20;
                $this->pedagioData = "31/12/2021";

                # Datas
                $this->dtIngresso = "31/12/2003";
                $this->dataLei = "01/01/2022";

                # Remuneração
                $this->calculoInicial = "Última remuneração";
                $this->teto = "Remuneração do servidor no cargo efetivo";
                $this->reajuste = "Na mesma data e índice dos servidores ativos";
                $this->paridade = "COM PARIDADE";

                # Cartilha
                $this->cartilha1 = "transicaoPedagio11.png";
                $this->cartilha2 = "transicaoPedagio12.png";
                break;

            ######################################3

            case "pedagio2" :

                # Descrição
                $this->tipo = "Regra de Transição";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra do Pedágio - Média";
                $this->descricaoResumida1 = "Pedágio";
                $this->descricaoResumida2 = "Média";
                $this->legislacao = "Artigo 4º da EC nº 90/2021.";

                # Regras
                $this->idadeHomem = 60;
                $this->idadeMulher = 55;
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 20;
                $this->cargoEfetivo = 5;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Pedagio
                $this->pedagio = 20;
                $this->pedagioData = "31/12/2021";

                # Datas
                $this->dtIngresso = "31/12/2021";
                $this->dataLei = "01/01/2022";

                # Remuneração
                $this->calculoInicial = "Média aritmética simples das 80% maiores remunerações a partir de julho de 1994";
                $this->teto = "Remuneração do servidor no cargo efetivo";
                $this->reajuste = "INPC - LEI 6.2442012";
                $this->paridade = "SEM PARIDADE";

                # Cartilha
                $this->cartilha1 = "transicaoPedagio21.png";
                $this->cartilha2 = "transicaoPedagio22.png";
                break;

            ######################################3

            case "pedagio3" :

                # Descrição
                $this->tipo = "Regra de Transição";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra do Pedágio com Redutor de Idade - Integralidade e Paridade";
                $this->descricaoResumida1 = "Pedágio";
                $this->descricaoResumida2 = "Redutor de Idade";
                $this->legislacao = "§5º do artigo 4º da EC nº 90/2021.";

                # Regras
                $this->idadeHomem = 60;
                $this->idadeMulher = 55;
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 20;
                $this->cargoEfetivo = 5;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Pedagio
                $this->pedagio = 20;
                $this->pedagioData = "31/12/2021";

                # Redutor
                $this->temRedutor = true;

                # Datas
                $this->dtIngresso = "16/12/1998";
                $this->dataLei = "01/01/2022";

                # Remuneração
                $this->calculoInicial = "Última remuneração";
                $this->teto = "Remuneração do servidor no cargo efetivo";
                $this->reajuste = "Na mesma data e índice dos servidores ativos";
                $this->paridade = "COM PARIDADE";

                # Cartilha
                $this->cartilha1 = "transicaoPedagio31.png";
                $this->cartilha2 = "transicaoPedagio32.png";
                break;

            ######################################3

            case "adquirido1" :

                # Descrição
                $this->tipo = "Direito Adquirido";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição";
                $this->descricaoResumida1 = "C.F. Art. 40";
                $this->descricaoResumida2 = "§1º, III, alínea a.";
                $this->legislacao = "C.F. Art. 40, §1º, III, alínea a.";

                # Regras
                $this->idadeHomem = 60;
                $this->idadeMulher = 55;
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 10;
                $this->cargoEfetivo = 5;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Datas
                $this->dtRequesitosCumpridos = "31/12/2021";
                #$this->dtIngressoApartir = "31/12/2003"; // Retirado a pedido de Simone
                # Remuneração
                $this->calculoInicial = "Média aritmética simples dos 80% das maiores remunerações de contribuições corrigidas desde julho/94 - Lei Federal 10.887";
                $this->teto = "Última remuneração do servidor no cargo efetivo";
                $this->reajuste = "Os proventos deverão ser reajustados na mesma data e índice adotados para o reajuste dos benefícios do regime geral de previdência social";
                $this->paridade = "SEM PARIDADE";

                # Cartilha
                $this->cartilha1 = "direitoAdquirido1.jpg";
                break;

            ######################################3

            case "adquirido2" :

                # Descrição
                $this->tipo = "Direito Adquirido";
                $this->descricao = "Aposentadoria por Idade";
                $this->descricaoResumida1 = "C.F. Art. 40";
                $this->descricaoResumida2 = "§1º, III, alínea b.";
                $this->legislacao = "C.F. Art. 40, §1º, III, alínea b.";

                # Regras
                $this->idadeHomem = 65;
                $this->idadeMulher = 60;
                $this->servicoPublico = 10;
                $this->cargoEfetivo = 5;

                # Abono Permanência
                $this->permiteAbonoPermanencia = false;

                # Datas
                $this->dtRequesitosCumpridos = "31/12/2021";
                #$this->dtIngressoApartir = "31/12/2003";   // retirado a pedido de Simone
                # Remuneração
                $this->calculoInicial = "Média aritmética simples dos 80% das maiores remunerações de contribuições corrigidas desde julho/94 - Proporcional ao tempo de contribuição - Lei Federal 10.887";
                $this->teto = "Remuneração do servidor no cargo efetivo";
                $this->reajuste = "INPC – Aplicado em Janeiro – Lei 6.244/2012";
                $this->paridade = "SEM PARIDADE";

                # Cartilha
                $this->cartilha1 = "direitoAdquirido2.jpg";
                break;

            ######################################3

            case "adquirido3" :

                # Descrição
                $this->tipo = "Direito Adquirido";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição";
                $this->descricaoResumida1 = "Art. 6 da EC nº 41/2003";
                $this->legislacao = "Art. 6 da EC nº 41/2003";

                # Regras
                $this->idadeHomem = 60;
                $this->idadeMulher = 55;
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 20;
                $this->cargoEfetivo = 5;
                $this->carreira = 10;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Datas
                $this->dtRequesitosCumpridos = "31/12/2021";
                $this->dtIngresso = "31/12/2003";

                # Remuneração
                $this->calculoInicial = "Integralidade dos Proventos";
                $this->teto = "Última remuneração no cargo efetivo do servidor";
                $this->paridade = "COM PARIDADE";

                # Cartilha
                $this->cartilha1 = "direitoAdquirido3.jpg";
                break;

            ######################################3

            case "adquirido4" :

                # Descrição
                $this->tipo = "Direito Adquirido";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Com Redutor de Idade";
                $this->descricaoResumida1 = "Art. 3 da EC nº 47/2005";
                $this->descricaoResumida2 = "Com Redutor de Idade";
                $this->legislacao = "Art. 3 da EC nº 47/2005";

                # Regras
                $this->idadeHomem = 60;
                $this->idadeMulher = 55;
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 25;
                $this->cargoEfetivo = 5;
                $this->carreira = 15;

                # Abono Permanência
                $this->permiteAbonoPermanencia = true;

                # Redutor
                $this->temRedutor = true;

                # Datas
                $this->dtRequesitosCumpridos = "31/12/2021";
                $this->dtIngresso = "16/12/1998";

                # Remuneração
                $this->calculoInicial = "Integralidade dos Proventos";
                $this->teto = "Última remuneração no cargo efetivo do servidor";
                $this->paridade = "COM PARIDADE";

                # Cartilha
                $this->cartilha1 = "direitoAdquirido4.jpg";
                break;

            #####################################
        }

        # idServidor
        if (!empty($idServidor)) {
            $this->idServidor = $idServidor;
            $this->fazAnalise($idServidor);
        }

        # Modalidade
        $this->modalidade = $modalidade;
    }

    ###########################################################
    #
    ###########################################################   

    public function fazAnalise($idServidor) {

        if (!empty($idServidor)) {
            $this->idServidor = $idServidor;
        }

        # Inicializa a flag
        $this->temDireito = true;

        ####################################################################

        /*
         * Dados pessoais do servidor
         */

        $pessoal = new Pessoal();

        # Idade
        $this->servidorIdade = $pessoal->get_idade($this->idServidor);

        # Data do Nascimento
        $this->servidorDataNascimento = $pessoal->get_dataNascimento($this->idServidor);

        # Sexo
        $this->servidorSexo = $pessoal->get_sexo($this->idServidor);

        ####################################################################

        /*
         * Tempo Averbado
         */
        $averbacao = new Averbacao();

        # Tempo Público
        $this->servidorTempoAverbadoPublico = $averbacao->get_tempoAverbadoPublico($this->idServidor);

        # Tempo Privado
        $this->servidorTempoAverbadoPrivado = $averbacao->get_tempoAverbadoPrivado($this->idServidor);

        ####################################################################

        /*
         * Dados Uenf
         */

        $aposentadoria = new Aposentadoria();

        # Tempo Uenf
        $this->servidorTempoUenf = $aposentadoria->get_tempoServicoUenf($this->idServidor);

        # Data de ingresso
        $this->servidorDataIngresso = $aposentadoria->get_dtIngresso($this->idServidor);

//        # Altera a data de ingresso para o servidor que tem tempo celetista Uenf 
//        if ($aposentadoria->get_tempoServicoUenfCeletista($idServidor) > 0) {
//            # Retorna a data da transformação em estatutários
//            # Daqueles que entraram com celetistas na Uenf
//            $this->servidorDataIngresso = "09/09/2003";
//            
//            $this->dtIngressoObs = "A data de ingresso foi alterada para 09/09/2003 seguindo determinação do Rio Previdência. *";
//            
//            $this->mensagem = "* O Rio Previdência considera, para definição da data de ingresso no serviço público, somente o tempo como estatutário.<br/>"
//                        . "Dessa forma, todo servidor, admitido na Uenf antes de 09/09/2003, como celetista, tem considerada a data 09/09/2003 como a de ingresso no serviço público.";
//        } # Alterado pois Simone me informou que o Vagner e Lívia
//          # disseram que no caso de contratos CLT - Uenf, a data de 
//          # efetivo exercício público retroage a data de admissão
        # Tempo de contribuição Geral - Sem considerar os afastamentos sem contribuição
        $this->servidorTempoContribuicao = $this->servidorTempoAverbadoPublico + $this->servidorTempoAverbadoPrivado + $this->servidorTempoUenf;

        # Tempo de contribuição Real - Considerando os afastamentos sem contribuição
        $this->servidorTempoContribuicaoDescontado = $this->servidorTempoContribuicao - $aposentadoria->get_semTempoServicoSemTempoContribuicao($this->idServidor);

        # Tempo Initerrupto
        $this->servidorTempoPublicoIninterrupto = $aposentadoria->get_tempoPublicoIninterrupto($this->idServidor);

        # Tempo Publico
        $this->servidorTempoPublico = $this->servidorTempoAverbadoPublico + $this->servidorTempoUenf - $aposentadoria->get_tempoAfastadoComContribuicao($idServidor);

        # Especifica a regra de idade e de tempo de contribuição
        if ($this->servidorSexo == "Masculino") {
            $this->regraContribuicao = $this->contribuicaoHomem;

            # Verifica se tem divisor de idade
            if (empty($this->dataDivisorIdade)) {
                $this->regraIdade = $this->idadeHomem;
            } else {
                # Calcula a data
                $dataTemporaria = addAnos($this->servidorDataNascimento, $this->idadeHomemAntes);

                if (year($this->dataDivisorIdade) <= year($dataTemporaria)) {
                    $this->regraIdade = $this->idadeHomemDepois;
                } else {
                    $this->regraIdade = $this->idadeHomemAntes;
                }
            }
        } else {
            $this->regraContribuicao = $this->contribuicaoMulher;

            # Verifica se tem divisor de idade
            if (empty($this->dataDivisorIdade)) {
                $this->regraIdade = $this->idadeMulher;
            } else {
                # Calcula a data
                $dataTemporaria = addAnos($this->servidorDataNascimento, $this->idadeMulherAntes);
                if (year($this->dataDivisorIdade) <= year($dataTemporaria)) {
                    $this->regraIdade = $this->idadeMulherDepois;
                } else {
                    $this->regraIdade = $this->idadeMulherAntes;
                }
            }
        }

        # Pega a data de hoje
        $hoje = date("d/m/Y");

        # Data da Aposentadoria Compulsoria
        $this->dataCompulsoria = $aposentadoria->get_dataAposentadoriaCompulsoria($this->idServidor);

        ####################################################################

        /*
         * Data de Ingresso
         */

        if (!empty($this->dtIngresso)) {
            if (dataMaior($this->dtIngresso, $this->servidorDataIngresso) == $this->dtIngresso) {
                $this->analisaDtIngresso = "OK";
            } else {
                $this->analisaDtIngresso = "Não Tem Direito";
                $this->temDireito = false;
            }
        }

        ####################################################################

        /*
         * Data de Ingresso a partir
         */

        if (!empty($this->dtIngressoApartir)) {
            if (dataMaior($this->dtIngressoApartir, $this->servidorDataIngresso) == $this->servidorDataIngresso) {
                $this->analisaDtIngressoApartir = "OK";
            } else {
                $this->analisaDtIngressoApartir = "Não Tem Direito";
                $this->temDireito = false;
            }
        }

        ####################################################################

        /*
         *  Idade
         */

        $this->dataCriterioIdade = addAnos($this->servidorDataNascimento, $this->regraIdade);
        if ($this->servidorIdade >= $this->regraIdade) {
            $this->analiseIdade = "OK";
        } else {
            # Calcula a data
            $this->analiseIdade = "Ainda faltam<br/>" . dataDif(date("d/m/Y"), $this->dataCriterioIdade) . " dias.";
        }

        ####################################################################

        /*
         *  Tempo de Contribuição
         */

        # Pega o tempo que falta em dias
        $resta1 = ($this->regraContribuicao * 365) - $this->servidorTempoContribuicao;

        # Calcula a data - retiro a contagem do primeiro dia para não contar hoje 2 vezes
        $this->dataCriterioTempoContribuicao = addDias($hoje, $resta1, false);

        # Verifica se considerando a data, o servidor tem algum afastamento 
        $diasSemContribuicao = $aposentadoria->get_semTempoServicoSemTempoContribuicao($this->idServidor, $this->dataCriterioTempoContribuicao);

        if ($diasSemContribuicao > 0) {
            # Pega a data antes da alteração
            $this->dataCriterioTempoContribuicaoOriginal = $this->dataCriterioTempoContribuicao;

            # Acrescenta os dias para compensar o tempo de afastamentos sem contribuição
            $this->dataCriterioTempoContribuicao = addDias($this->dataCriterioTempoContribuicao, $diasSemContribuicao, false);
            $this->obsContribuicao = "Tendo em vista {$diasSemContribuicao} dias de afastamento sem contribuição, a data foi alterada, de {$this->dataCriterioTempoContribuicaoOriginal} para {$this->dataCriterioTempoContribuicao}.";

            # Atualiza os dias
            $resta1 = getNumDias($hoje, $this->dataCriterioTempoContribuicao);
        }

        # Verifica se atende ou não os requisitos
        #if ($this->servidorTempoContribuicao >= ($this->regraContribuicao * 365)) {
        if (jaPassou($this->dataCriterioTempoContribuicao)) {
            $this->analiseContribuicao = "OK";
        } else {
            $this->analiseContribuicao = "Ainda faltam<br/>{$resta1} dias.";
        }

        ####################################################################

        /*
         *  Tempo de Carreira
         */
        # Pega o tempo que falta em dias
        $resta1 = ($this->carreira * 365) - $this->servidorTempoUenf;

        # Calcula a data - retiro a contagem do primeiro dia para não contar hoje 2 vezes
        $this->dataCriterioCarreira = addDias($hoje, $resta1, false);

        # Verifica se considerando a data, o servidor tem algum afastamento 
        $diasSemTempoServico = $aposentadoria->get_semTempoServico($this->idServidor, $this->dataCriterioCarreira);

        if ($diasSemTempoServico > 0) {
            # Pega a data antes da alteração
            $this->dataCriterioCarreiraOriginal = $this->dataCriterioCarreira;

            # Acrescenta os dias para compensar o tempo de afastamentos sem contribuição
            $this->dataCriterioCarreira = addDias($this->dataCriterioCarreira, $diasSemTempoServico, false);
            $this->obsCarreira = "Tendo em vista {$diasSemTempoServico} dias de afastamento sem contribuição, a data foi alterada, de {$this->dataCriterioCarreiraOriginal} para {$this->dataCriterioCarreira}.";

            # Atualiza os dias
            $resta1 = getNumDias($hoje, $this->dataCriterioCarreira);
        }

        # Verifica se atende ou não os requisitos
        if ($this->servidorTempoUenf >= ($this->carreira * 365)) {
            $this->analiseCarreira = "OK";
        } else {
            $this->analiseCarreira = "Ainda faltam<br/>{$resta1} dias.";
        }

        ####################################################################

        /*
         *  Serviço Público
         */
        # Pega o tempo que falta em dias
        $resta2 = ($this->servicoPublico * 365) - $this->servidorTempoPublico;

        # Calcula a data - retiro a contagem do primeiro dia para não contar hoje 2 vezes
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2, false);

        # Verifica se considerando a data, o servidor tem algum afastamento 
        $diasSemTempoServico = $aposentadoria->get_semTempoServico($this->idServidor, $this->dataCriterioTempoServicoPublico);

        if ($diasSemTempoServico > 0) {
            # Pega a data antes da alteração
            $this->dataCriterioTempoServicoPublicoOriginal = $this->dataCriterioTempoServicoPublico;

            # Acrescenta os dias para compensar o tempo de afastamentos sem contribuição
            $this->dataCriterioTempoServicoPublico = addDias($this->dataCriterioTempoServicoPublico, $diasSemTempoServico, false);
            $this->obsServicoPublico = "Tendo em vista {$diasSemTempoServico} dias de afastamento sem contribuição, a data foi alterada, de {$this->dataCriterioTempoServicoPublicoOriginal} para {$this->dataCriterioTempoServicoPublico}.";

            # Atualiza os dias
            $resta2 = getNumDias($hoje, $this->dataCriterioTempoServicoPublico);
        }

        #if ($this->servidorTempoPublico >= ($this->servicoPublico * 365)) {
        if (jaPassou($this->dataCriterioTempoServicoPublico)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam<br/>{$resta2} dias.";
        }

        ####################################################################

        /*
         *  Cargo Efetivo
         */
        # Pega o tempo que falta em dias
        $resta3 = ($this->cargoEfetivo * 365) - $this->servidorTempoUenf;

        # Calcula a data - retiro a contagem do primeiro dia para não contar hoje 2 vezes
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3, false);

        # Verifica se considerando a data, o servidor tem algum afastamento 
        $diasSemTempoServico = $aposentadoria->get_semTempoServico($this->idServidor, $this->dataCriterioTempoCargo);

        if ($diasSemTempoServico > 0) {
            # Pega a data antes da alteração
            $this->dataCriterioTempoCargoOriginal = $this->dataCriterioTempoCargo;

            # Acrescenta os dias para compensar o tempo de afastamentos sem contribuição
            $this->dataCriterioTempoCargo = addDias($this->dataCriterioTempoCargo, $diasSemTempoServico, false);
            $this->obsTempoCargo = "Tendo em vista {$diasSemTempoServico} dias de afastamento sem contribuição, a data foi alterada, de {$this->dataCriterioTempoCargoOriginal} para {$this->dataCriterioTempoCargo}.";

            # Atualiza os dias
            $resta3 = getNumDias($hoje, $this->dataCriterioTempoCargo);
        }

        if ($this->servidorTempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam<br/>" . abs($resta3) . " dias.";
        }

        ####################################################################

        /*
         *  Pontos
         */

        if (!empty($this->pontosHomem)) {
            # Define os anos
            $anoFinal = 2051;
            $anoAtual = date("Y");

            # Calcula a data do critério de pontos
            for ($i = $anoAtual; $i <= $anoFinal; $i++) {

                # Pega os pontos da regra para o ano $i
                $pontosRegra = $this->get_regraPontos($i);

                # Pega os pontos possíveis nesse mesmo ano
                $pontosPossíveis = $this->get_pontoPossivel($i);

                # Pega os Pontos Atuais
                $this->servidorPontos = $this->get_pontoAtual();

                # Se alcançou com a data maior
                if ($pontosPossíveis == $pontosRegra) {

                    # Data do aniversário
                    $data1 = day($this->servidorDataNascimento) . "/" . month($this->servidorDataNascimento) . "/" . $i;

                    # Data do tempo de contribuicao
                    $diasContribuicao = $this->get_contribuicaoPosivel($i) * 365;

                    $diasqueResta = $diasContribuicao - $this->servidorTempoContribuicaoDescontado;
                    $data2 = addDias($hoje, $diasqueResta, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
                    # Verifica o mais distante.
                    $this->dataCriterioPontos = dataMaior($data1, $data2);
                    break;
                }

                # Se alcançou com a data menor
                if ($pontosPossíveis > $pontosRegra) {

                    # Data do aniversário
                    $data1 = day($this->servidorDataNascimento) . "/" . month($this->servidorDataNascimento) . "/" . $i;

                    # Data do tempo de contribuicao
                    $diasContribuicao = $this->get_contribuicaoPosivel($i) * 365;
                    $diasqueResta = $diasContribuicao - $this->servidorTempoContribuicaoDescontado;
                    $data2 = addDias($hoje, $diasqueResta, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes

                    $this->dataCriterioPontos = dataMenor($data1, $data2);
                    break;
                }
            }

            if ($this->servidorPontos >= $this->get_regraPontos(date("Y"))) {
                $this->analisePontos = "OK";
                //$this->dataCriterioPontos = null; // retira a data pois dava erro ao calcular datas passadas
            } else {
                # Pega o resto
                $resta4 = $this->get_regraPontos(date("Y")) - $this->servidorPontos;
                $this->analisePontos = "Ainda faltam<br/>{$resta4} pontos.";
            }
        }

        ####################################################################

        /*
         * Pedágio
         */

        # Verifica se tem pedágio na regra que está sendo exibida
        if (!empty($this->pedagio)) {
            # Pega o tempo em dias antes da data alvo do pedágio para ver se tem direito a esta regra
            $this->servidorTempoAntesData = $aposentadoria->get_tempoTotalAntesDataAlvo($this->idServidor, $this->pedagioData);

            # Pega o que sobra
            $this->servidorTempoSobra = ($this->regraContribuicao * 365) - $this->servidorTempoAntesData;

            $this->servidorPedagio = round($this->servidorTempoSobra * ($this->pedagio / 100));

            # Ajeita a descrição
            $this->pedagioDescricao .= $this->pedagioData;

            if ($this->servidorPedagio < 0) {
                $this->dataCriterioPedagio = "---";
                $this->analisePedagio = "OK";
            } else {
                $this->dataCriterioPedagio = addDias($this->dataCriterioTempoContribuicao, $this->servidorPedagio, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes

                if (jaPassou($this->dataCriterioPedagio)) {
                    $this->analisePedagio = "OK";
                } else {
                    $resta4 = getNumDias($hoje, $this->dataCriterioPedagio);
                    $this->analisePedagio = "Ainda faltam<br/>{$resta4} dias.";
                }
            }
        }

        ####################################################################

        /*
         * Redutor
         */

        if ($this->temRedutor) {
            # Verifica se a data do critério idade é maior que o critério tempo (para ver se vale a pena a redução)
            if (dataMaior($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade) == $this->dataCriterioIdade) {

                # Verifica o tempo de contribuição excedente até hoje
                $this->tempoExcedente = dataDif($this->dataCriterioTempoContribuicao, date("d/m/Y"));

                # Tempo que falta
                # intval -> arredonda pra baixo
                # ceil   -> arredonda pra cima
                $this->diasIdadeQueFalta = dataDif($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade);
                $this->mesesIdadeQueFalta = ceil($this->diasIdadeQueFalta / 30);

                # Tempo pra pagar
                $this->diasParaPagar = intval($this->diasIdadeQueFalta / 2);
                $this->mesesParaPagar = ceil($this->diasParaPagar / 30);

                # Calcula a data de idade imediatamente posterior a da contribuicao
                $this->dataIdadeAposContribuicao = $this->dataCriterioIdade;
                $contadorX = 1;

                do {
                    $this->dataIdadeAposContribuicao = addMeses($this->dataCriterioIdade, $contadorX);
                    $contadorX--;
                } while (dataMaior($this->dataIdadeAposContribuicao, $this->dataCriterioTempoContribuicao) == $this->dataIdadeAposContribuicao);

                # sobe um mes
                $this->dataIdadeAposContribuicao = addMeses($this->dataIdadeAposContribuicao, 1);

                # Faz o cálculo da data com o redutor com relacao ao meses da idade que faltam:
                if (epar($this->mesesIdadeQueFalta)) {
                    # se for par -> termina em uma data de contribuiçao
                    $this->dataCriterioRedutor = addMeses($this->dataCriterioTempoContribuicao, $this->mesesParaPagar);
                } else {
                    # se for impar -> termina em uma data de idade e conta um mes antes pois começa do um)
                    $this->dataCriterioRedutor = addMeses($this->dataIdadeAposContribuicao, $this->mesesParaPagar);
                }

                # Muda a análise do critério idade
                $this->mensagemRedutor = "<br/><hr/ id='hrPrevisaoAposentAnalise'><p id='pLinha2'>Com Redutor</p>" . $this->dataCriterioRedutor;

                if (jaPassou($this->dataCriterioRedutor)) {
                    $this->analiseIdade = "OK";
                } else {
                    # Calcula a data
                    $this->analiseIdade = "Ainda faltam<br/>" . dataDif(date("d/m/Y"), $this->dataCriterioRedutor) . " dias.<hr id='geral' />Somente em {$this->dataCriterioRedutor}.";
                }
            } else {
                $this->analiseReducao = "Não cabe o uso do redutor pois o servidor cumpriu o requisito de idade antes do de tempo de contribuição.";
            }
        }

        ####################################################################

        /*
         *  Data do Direito a Aposentadoria
         */

        # Define as datas que serão comparadas
        $arrayDatas = [
            $this->dataCriterioTempoContribuicao,
            $this->dataCriterioTempoServicoPublico,
            $this->dataCriterioTempoCargo,
            $this->dataCriterioPontos,
            $this->dataCriterioPedagio,
            $this->dataCriterioRedutor,
            $this->dataCriterioCarreira
        ];

        # Define a data do critério idade quando tem redução
        if (empty($this->dataCriterioRedutor)) {
            array_unshift($arrayDatas, $this->dataCriterioIdade);
        } else {
            if (dataMaior($this->dataCriterioRedutor, $this->dataCriterioIdade) == $this->dataCriterioIdade) {
                array_unshift($arrayDatas, $this->dataCriterioRedutor);
            }
        }

        # Define a data maior
        $this->dataDireitoAposentadoria = dataMaiorArray($arrayDatas);

        # Ajusta a data quando for antes da data da Lei
        if (dataMaior($this->dataDireitoAposentadoria, $this->dataLei) == $this->dataLei) {
            $this->dataDireitoAposentadoria = $this->dataLei;
            $this->ajustado = true;
        }

        # Data limite do cumprimento dos requisitos
        if (!empty($this->dtRequesitosCumpridos)) {
            if (dataMaior($this->dtRequesitosCumpridos, $this->dataDireitoAposentadoria) == $this->dtRequesitosCumpridos) {
                $this->analiseDtRequesitosCumpridos = "OK";
            } else {
                $this->analiseDtRequesitosCumpridos = "Não Tem Direito";
                $this->temDireito = false;
            }
        }

        # Define o texto de retorno    
        if ($this->analiseDtRequesitosCumpridos == "OK" OR $this->dtRequesitosCumpridos == null) {
            if (jaPassou($this->dataDireitoAposentadoria)) {
                $this->textoRetorno = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->textoReduzido = "Desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->corFundo = "success";
                $this->temDireito = true;
            } else {
                $this->textoRetorno = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->textoReduzido = "Somente em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->corFundo = "warning";
                $this->temDireito = true;
            }
        } else {
            $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $this->textoReduzido = "<b>Não Tem Direito</b>";
            $this->corFundo = "alert";
            $this->temDireito = false;
        }

        # Verifica a regra extra da data de ingresso
        if ($this->analisaDtIngresso == "Não Tem Direito") {
            $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $this->textoReduzido = "<b>Não Tem Direito</b>";
            $this->corFundo = "alert";
            $this->temDireito = false;
        }

        # Verifica a regra extra da data de ingresso A partir
        if ($this->analisaDtIngressoApartir == "Não Tem Direito") {
            $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $this->textoReduzido = "<b>Não Tem Direito</b>";
            $this->corFundo = "alert";
            $this->temDireito = false;
        }

        # Verifica com a data da Aposentadoria Compulsoria
        if ($this->verificaCompulsoria) {
            $dataCompulsoria = $aposentadoria->get_dataAposentadoriaCompulsoria($this->idServidor);

            if (dataMaior($this->dataDireitoAposentadoria, $dataCompulsoria) == $this->dataDireitoAposentadoria) {
                $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
                $this->textoReduzido = "<b>Não Tem Direito</b>";
                $this->corFundo = "alert";
                $this->temDireito = false;

                $this->analiseCompulsoria = "Não Tem Direito";
            } else {
                $this->analiseCompulsoria = "OK";
            }
        }
    }

    ###########################################################

    public function exibe_tabelaDados($relatorio = false) {

        # Verifica se tem pontos
        if (!empty($this->pontosHomem)) {
            $regraPontos = $this->get_regraPontos(date("Y"));
        }

        /*
         *  Tabela
         */

        # Idade (todos tem)
        $array = [
            ["Idade",
                $this->idadeDescricao,
                "{$this->regraIdade} anos",
                "{$this->servidorIdade} anos<br/>({$this->servidorDataNascimento})",
                $this->dataCriterioIdade . $this->mensagemRedutor,
                $this->analiseIdade,
                null],
        ];

        # Data de Ingresso (se tiver)      
        if (!is_null($this->dtIngresso)) {
            array_unshift($array,
                    ["Data de Ingresso",
                        $this->dtIngressoDescricao,
                        $this->dtIngresso,
                        $this->servidorDataIngresso,
                        "---",
                        $this->analisaDtIngresso,
                        $this->dtIngressoObs]);
        }

        # Data de Ingresso A partir (se tiver)      
        if (!empty($this->dtIngressoApartir)) {
            array_unshift($array,
                    ["Data de Ingresso",
                        $this->dtIngressoApartirDescricao,
                        $this->dtIngressoApartir,
                        $this->servidorDataIngresso,
                        "---",
                        $this->analisaDtIngressoApartir,
                        null]);
        }

        # Tempo de carreira (se tiver)
        if (!is_null($this->carreira)) {
            array_push($array,
                    ["Carreira",
                        $this->carreiraDescricao,
                        "{$this->carreira} anos<br/>(" . ($this->carreira * 365) . " dias)",
                        intval($this->servidorTempoUenf / 365) . " anos<br/>({$this->servidorTempoUenf} dias)",
                        $this->dataCriterioCarreira,
                        $this->analiseCarreira,
                        $this->obsCarreira]);
        }

        # Tempo de Contribuição (se tiver)
        if (!is_null($this->contribuicaoHomem)) {
            array_push($array,
                    ["Contribuição",
                        $this->tempoContribuiçãoDescricao,
                        "{$this->regraContribuicao} anos<br/>(" . ($this->regraContribuicao * 365) . " dias)",
                        intval($this->servidorTempoContribuicaoDescontado / 365) . " anos<br/>({$this->servidorTempoContribuicaoDescontado} dias)",
                        $this->dataCriterioTempoContribuicao,
                        $this->analiseContribuicao,
                        $this->obsContribuicao]);
        }

        # Pontos (se tiver)
        if (!is_null($this->pontosHomem)) {
            array_push($array, ["Pontuação",
                "Pontuação Atual (" . date("Y") . ")",
                "{$regraPontos} pontos",
                "{$this->servidorPontos} pontos<br/>({$this->servidorIdade} + " . intval($this->servidorTempoContribuicaoDescontado / 365) . ")",
                trataNulo($this->dataCriterioPontos),
                $this->analisePontos,
                null]);
        }

        # Pedágio (se tiver)
        if (!is_null($this->pedagio)) {
            # Trata a informação
            if ($this->servidorPedagio > 0) {
                $textoPedagio = "Faltava {$this->servidorPedagio} dias em " . addDias($this->pedagioData, 1, false);
            } else {
                $textoPedagio = "Sobravam " . abs($this->servidorPedagio) . "  dias em " . addDias($this->pedagioData, 1, false);
            }

            array_push($array, ["Pedágio",
                $this->pedagioDescricao,
                "{$this->pedagio} %",
                $textoPedagio,
                $this->dataCriterioPedagio,
                $this->analisePedagio,
                null]);
        }

        # Tempo Público (se tiver)
        if (!is_null($this->servicoPublico)) {
            array_push($array, ["Serviço Público",
                $this->tempoPublicoDescicao,
                "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)",
                "{$this->servidorTempoPublico} dias",
                $this->dataCriterioTempoServicoPublico,
                $this->analisePublico,
                $this->obsServicoPublico]);
        }

        # Cargo Efetivo (se tiver)
        if (!is_null($this->cargoEfetivo)) {
            array_push($array, ["Cargo Efetivo",
                $this->tempoCargoDescicao,
                "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)",
                "{$this->servidorTempoUenf} dias",
                $this->dataCriterioTempoCargo,
                $this->analiseCargoEfetivo,
                $this->obsTempoCargo]);
        }

        # Data Limite
        if (!is_null($this->dtRequesitosCumpridos)) {
            array_push($array, ["Data Limite",
                $this->dtRequesitosCumpridosDescicao,
                $this->dtRequesitosCumpridos,
                $this->dataDireitoAposentadoria,
                "---",
                $this->analiseDtRequesitosCumpridos,
                null]);
        }

        # Aposentadoria Compulsória
        if ($this->verificaCompulsoria) {
            # Faz a análise
            array_push($array, ["Aposentadoria Compulsória",
                $this->compulsoriaDescricao,
                "{$this->dataCompulsoria}<br/>(Compulsória)",
                $this->dataDireitoAposentadoria,
                "---",
                $this->analiseCompulsoria,
                null]);
        }

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Dados");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Dados");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Item", "Descrição", "Regra", "Servidor", "Data", "Análise", "Obs"]);
        $tabela->set_width([11, 23, 11, 11, 11, 11, 22]);
        $tabela->set_align(["left", "left", "center", "center", "center", "center", "left"]);
        $tabela->set_totalRegistro(false);

        if (!$relatorio) {
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 5,
                    'valor' => 'OK',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 5,
                    'valor' => "Não Tem Direito",
                    'operador' => '=',
                    'id' => 'naoPode'),
                array('coluna' => 5,
                    'valor' => 'OK',
                    'operador' => '<>',
                    'id' => 'podera')
            ));
        }
        $tabela->show();

        # Mensagem
        if (!empty($this->mensagem)) {
            if ($relatorio) {
                p($this->mensagem, "left", "f12");
            } else {
                titulotable("Observações Importantes");
                callout($this->mensagem);
            }
        }

        # Data da Aposentadoria 
        if ($this->ajustado) {
            $msgAjustado = "A data da aposentadoria foi ajustada para {$this->dataLei}, pois, nessa modalidade de aposentadoria, a data não pode ser anterior a data da Lei Complementar nº 195/2021";
            if ($relatorio) {
                tituloRelatorio("Atenção");
                $painel = new Callout("secondary");
                $painel->abre();
                p($msgAjustado, "left", "f12");
                $painel->fecha();
            } else {
                tituloTable("Atenção");
                $painel = new Callout("warning");
                $painel->abre();
                p($msgAjustado, "center");
                $painel->fecha();
            }
        }
    }

    ###########################################################

    public function exibe_analise($relatorio = false, $idServidor = null) {

        $painel = new Callout($this->corFundo);
        $painel->abre();
        p($this->textoRetorno, "center");
        $painel->fecha();
    }

    ###########################################################

    public function exibe_analiseRelatorio() {

        return $this->textoRetorno;
    }

    ###########################################################

    public function exibe_analiseLink($idServidor = null, $link = null, $resumido = true) {

        # Faz a análise
        $this->fazAnalise($idServidor);

        echo "<a href='{$link}'>";

        if ($resumido) {
            tituloTable($this->descricaoResumida1, null, $this->descricaoResumida2);
        } else {
            tituloTable("{$this->tipo}<br/>{$this->descricao}", null, $this->legislacao);
        }

        # Exibe o resumo
        $painel = new Callout($this->corFundo);

        if ($resumido) {
            $painel->abre();
            p($this->textoReduzido, "center");
            $painel->fecha();
        } else {
            p($this->exibe_analise(), "center");
        }


        echo "</a>";
    }

    ###########################################################

    public function get_dataAposentadoria($idServidor = null) {

        # Faz a análise
        if (!empty($idServidor)) {
            $this->fazAnalise($idServidor);
        }

        # Verifica se tem direito
        if ($this->temDireito) {
            return $this->dataDireitoAposentadoria;
        } else {
            return "---";
        }
    }

    ###########################################################

    public function get_diasFaltantes($idServidor = null) {

        # Faz a análise
        if (!empty($idServidor)) {
            $this->fazAnalise($idServidor);
        }

        # Verifica se tem direito
        if ($this->temDireito) {
            # Verifica se ja passou
            if (jaPassou($this->dataDireitoAposentadoria)) {
                return "0";
            } else {
                return dataDif(date("d/m/Y"), $this->dataDireitoAposentadoria);
            }
        } else {
            return "Não Tem Direito";
        }
    }

    ###########################################################

    public function exibe_tabelaRegras($relatorio = false) {

        # Idade
        if (empty($this->dataDivisorIdade)) {
            $array = [
                ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
            ];
        } else {
            $array = [
                ["<p id='pLinha1'>Idade<br/>Antes de {$this->dataDivisorIdade}</p><hr/ id='geral'><p id='pLinha1'>Depois de {$this->dataDivisorIdade}</p><p id='pLinha4'>{$this->idadeDescricao}</p>", "{$this->idadeMulherAntes} anos<hr/ id='geral'>{$this->idadeMulherDepois} anos<br/>", "{$this->idadeHomemAntes} anos<hr/ id='geral'>{$this->idadeHomemDepois} anos<br/>"],
            ];
        }

        # Data de ingresso
        if (!is_null($this->dtIngresso)) {
            array_unshift($array, ["<p id='pLinha1'>Data de Ingresso</p><p id='pLinha4'>{$this->dtIngressoDescricao}</p>", $this->dtIngresso, $this->dtIngresso]);
        }

        # Tempo de Contribuição
        if (!is_null($this->contribuicaoHomem)) {
            array_push($array, ["<p id='pLinha1'>Contribuição</p><p id='pLinha4'>{$this->tempoContribuiçãoDescricao}</p>", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"]);
        }

        # Pontos
        if (!is_null($this->pontosHomem)) {
            array_push($array, ["<p id='pLinha1'>Pontuação Iniciall</p><p id='pLinha4'>{$this->pontuacaoInicialDescricao}</p>", $this->pontosMulher . " pontos", $this->pontosHomem . " pontos"]);
        }

        # Pedágio
        if (!is_null($this->pedagio)) {
            array_push($array, ["<p id='pLinha1'>Pedágio</p><p id='pLinha4'>{$this->pedagioDescricao}</p>", $this->pedagio . " %", $this->pedagio . " %"]);
        }

        # Tempo público
        if (!is_null($this->servicoPublico)) {
            array_push($array, ["<p id='pLinha1'>Serviço Público</p><p id='pLinha4'>{$this->tempoPublicoDescicao}</p>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"]);
        }

        # Tempo cargo efetivo
        if (!is_null($this->cargoEfetivo)) {
            array_push($array, ["<p id='pLinha1'>Cargo Efetivo</p><p id='pLinha4'>{$this->tempoCargoDescicao}</p>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"]);
        }

        # Tempo carreira
        if (!is_null($this->cargoEfetivo)) {
            array_push($array, ["<p id='pLinha1'>Carreira</p><p id='pLinha4'>{$this->carreiraDescricao}</p>", $this->carreira . " anos<br/>(" . ($this->carreira * 365) . " dias)", $this->carreira . " anos<br/>(" . ($this->carreira * 365) . " dias)"]);
        }

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Regras Gerais");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Regras Gerais");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Requisito", "Mulher", "Homem"]);
        $tabela->set_width([50, 25, 25]);
        $tabela->set_align(["left"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_rodape("");
        $tabela->show();
    }

    ###########################################################

    public function exibe_tabelaRemuneração($relatorio = false) {

        $array = [
            ["Cálculo Inicial", $this->calculoInicial]
        ];

        # Verifica se tem percentual devidoFF
        if (!is_null($this->percentualDevido)) {
            array_push($array, ["Percentual Devido", $this->percentualDevido]);
        }

        # Verifica se tem teto
        if (!is_null($this->teto)) {
            array_push($array, ["Teto", $this->teto]);
        }

        # Coloca o restoF
        array_push($array, ["Reajuste", $this->reajuste]);
        array_push($array, ["Paridade", $this->paridade]);

        if ($this->permiteAbonoPermanencia) {
            array_push($array, ["Abono Permanência", "Sim"]);
        } else {
            array_push($array, ["Abono Permanência", "Não - Essa regra <b>NÃO</b> concede Abono Permanência!"]);
        }

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Remuneração");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Remuneração");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Item", "Descrição"]);
        $tabela->set_width([40, 60]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibe_tabelaRegrasPontos($relatorio = false) {

        $grid = new Grid();
        $grid->abreColuna(12);

        tituloTable("Regra dos Pontos");

        $grid->fechaColuna();
        $grid->abreColuna(6);

        # Exibe a tabela Masculina
        if ($relatorio) {
            $tabela = new Relatorio();
            tituloRelatorio("Masculino");
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Masculino");
        }

        $tabela->set_conteudo($this->tabelaM);
        $tabela->set_label(["Ano", "Pontos"]);
        $tabela->set_width([50, 50]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid->fechaColuna();
        $grid->abreColuna(6);

        # Exibe a tabela Masculina
        if ($relatorio) {
            tituloRelatorio("Feminino");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Feminino");
        }

        $tabela->set_conteudo($this->tabelaF);
        $tabela->set_label(["Ano", "Pontos"]);
        $tabela->set_width([50, 50]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    ###########################################################

    public function exibe_tempoAntesDataAlvo($relatorio = false) {

        $aposentadoria = new Aposentadoria();
        $averbacao = new Averbacao();

        $array = [
            ["Cargo Efetivo - Uenf", $aposentadoria->get_tempoServicoUenfAntesDataAlvo($this->idServidor, $this->pedagioData)],
            ["Tempo Averbado", $averbacao->getTempoAverbadoAntesDataAlvo($this->idServidor, $this->pedagioData)]
        ];

        # Tabela Tempo até Data Alvo
        if ($relatorio) {
            tituloRelatorio("Tempo até {$this->pedagioData}");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo até {$this->pedagioData}");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio(1);
        $tabela->show();
    }

    ###########################################################

    public function exibe_calculoPedagio($relatorio = false) {

        $array = [
            ["Contribuição até {$this->pedagioData}", "{$this->servidorTempoAntesData} dias"],
            ["Regra da Aposentadoria", ($this->regraContribuicao * 365) . " dias<br/>({$this->regraContribuicao} anos)"],
            ["Tempo que Faltava em " . addDias($this->pedagioData, 1, false), "{$this->servidorTempoSobra} dias"],
            ["Pedágio (20%)", $this->servidorPedagio . " dias"]
        ];

        # Cálculo do Pedágio
        if ($relatorio) {
            tituloRelatorio("Cálculo do Pedágio");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Cálculo do Pedágio");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Valor"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibe_cartilha() {


        # Verifica se tem cartilha
        if (!empty($this->cartilha1)) {

            $grid2 = new Grid();
            $grid2->abreColuna(12);

            tituloTable("Cartilha");

            # Verifica se tem uma ou duas imagens
            if (empty($this->cartilha2)) {
                $figura = new Imagem(PASTA_FIGURAS . $this->cartilha1, null, "100%", "100%");
                $figura->set_id('imgCasa');
                $figura->set_class('imagem');
                $figura->show();
            } else {

                $grid2->fechaColuna();
                $grid2->abreColuna(12, 6);

                $figura = new Imagem(PASTA_FIGURAS . $this->cartilha1, null, "100%", "100%");
                $figura->set_id('imgCasa');
                $figura->set_class('imagem');
                $figura->show();

                $grid2->fechaColuna();
                $grid2->abreColuna(12, 6);

                $figura = new Imagem(PASTA_FIGURAS . $this->cartilha2, null, "100%", "100%");
                $figura->set_id('imgCasa');
                $figura->set_class('imagem');
                $figura->show();
            }

            $grid2->fechaColuna();
            $grid2->fechaGrid();
        }
    }

    ###########################################################

    public function exibe_tabelaHistoricoPontuacao($relatorio = false) {

        # Define os anos
        $anoInicial = 2024;
        $anoFinal = 2051;
        $anoAtual = date("Y");

        for ($i = $anoAtual; $i <= $anoFinal; $i++) {

            # Pega os dados
            $pontos = $this->get_pontoPossivel($i);
            $pontosRegra = $this->get_regraPontos($i);
            $resta = $pontosRegra - $pontos;
            $demostrativo = $this->get_demonstrativoCalculoPontoPossivel($i);

            # Calcula a diferença
            if ($pontosRegra > $pontos) {
                $diferenca = "Ainda faltam {$resta} pontos";
            } else {
                $diferenca = "OK";
            }

            $array[] = [$i, $demostrativo, $pontos, $pontosRegra, $diferenca];

            if ($diferenca == "OK") {
                break;
            }
        }

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Cálculo da Pontuação");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Cálculo da Pontuação");
            $tabela->set_subtitulo("(A cada ano o servidor aumenta 2 pontos, a cada 2 anos a regra aumenta 1 ponto)");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Ano", "Cálculo<br/>Idade + Tempo", "Pontos do Servidor", "Regra", "Diferença"]);
        $tabela->set_width([18, 18, 18, 18, 28]);
        $tabela->set_totalRegistro(false);

        if (!$relatorio) {
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'operador' => '=',
                    'valor' => "OK",
                    'id' => 'vigente')));
        }
        $tabela->show();
    }

    ###########################################################

    private function get_regraPontos($ano = null) {

        # Trata o ano
        if (empty($ano)) {
            return null;
        }

        # Escolhe a tabela masculina
        if ($this->servidorSexo == "Masculino") {

            # Limite máximo
            if ($ano >= 2041) {
                return 105;
            }

            # Limite mínimo
            if ($ano <= 2022) {
                return 96;
            }

            # Busca o valor no array
            foreach ($this->tabelaM as $item) {
                if ($item[0] == $ano) {
                    return $item[1];
                }
            }
        } else {
            # Escolhe a tabela Feminina            
            # Limite máximo
            if ($ano >= 2049) {
                return 100;
            }

            # Limite mínimo
            if ($ano <= 2022) {
                return 86;
            }


            # Busca o valor no array
            foreach ($this->tabelaF as $item) {
                if ($item[0] == $ano) {
                    return $item[1];
                }
            }
        }
    }

    ###########################################################

    public function get_descricao() {

        return $this->descricao;
    }

    ###########################################################

    public function get_descricaoResumida1() {

        return $this->descricaoResumida1;
    }

###########################################################

    public function get_descricaoResumida2() {

        return $this->descricaoResumida2;
    }

    ###########################################################

    public function get_tipo() {

        return $this->tipo;
    }

    ###########################################################

    public function get_legislacao() {

        return $this->legislacao;
    }

    ###########################################################

    public function get_textoReduzido($idServidor) {

        # Faz a análise
        $this->fazAnalise($idServidor);

        # Retorna
        return $this->textoReduzido;
    }

    ###########################################################

    public function get_pontoPossivel($ano) {
        /*
         * Informa o ponto possível no ano informado
         */

        # Pega o ano de Nascimento
        $anoNascimento = year($this->servidorDataNascimento);

        # Pega o tempo de contribuição hoje
        $tempoContribuicaoHoje = $this->servidorTempoContribuicaoDescontado;

        # Soma com os dias possíveis do ano indicado
        $tempoPossivel = getNumDias(date("d/m/Y"), "31/12/{$ano}");

        # Passa para ano a soma dos tempos
        $tempo = intval(($tempoContribuicaoHoje + $tempoPossivel) / 365);

        return($ano - $anoNascimento) + $tempo;
    }

    ###########################################################

    public function get_demonstrativoCalculoPontoPossivel($ano) {
        /*
         * Exibe o demostrativo fde cálculo do ponto possível no ano intormado
         */

        # Pega o ano de Nascimento
        $anoNascimento = year($this->servidorDataNascimento);

        # Pega o tempo de contribuição hoje
        $tempoContribuicaoHoje = $this->servidorTempoContribuicaoDescontado;

        # Soma com os dias possíveis do ano indicado
        $tempoPossivel = getNumDias(date("d/m/Y"), "31/12/{$ano}");

        # Passa para ano a soma dos tempos
        $tempo = intval(($tempoContribuicaoHoje + $tempoPossivel) / 365);

        # Idade
        $idade = $ano - $anoNascimento;

        return "{$idade} + {$tempo}";
    }

    ###########################################################

    public function get_contribuicaoPosivel($ano) {

        /*
         * Informa a contribuição Possível do ano informado
         */

        # Pega o ano de Nascimento
        $anoNascimento = year($this->servidorDataNascimento);

        # Pega o tempo de contribuição hoje, em dias, sem considerar
        # os afastamentos que interrompem a contribuição
        $tempoContribuicaoHoje = $this->servidorTempoContribuicaoDescontado;

        # Soma com os dias possíveis do ano indicado
        $tempoPossivel = getNumDias(date("d/m/Y"), "31/12/{$ano}");

        # Passa para ano a soma dos tempos
        $tempo = intval(($tempoContribuicaoHoje + $tempoPossivel) / 365);

        return $tempo;
    }

    ###########################################################

    public function get_pontoAtual() {

        # Pega o tempo de contribuição


        return intval($this->servidorIdade + ($this->servidorTempoContribuicaoDescontado / 365));
    }

    ###########################################################

    public function exibe_tabelaCalculoRedutor($relatorio = false) {

        $aposentadoria = new Aposentadoria();
        $averbacao = new Averbacao();

        if (dataMaior($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade) == $this->dataCriterioIdade) {
            $array = [
                ["Data que completa o tempo de contribuição:", $this->dataCriterioTempoContribuicao],
                ["Data que completaria a idade:", $this->dataCriterioIdade],
                ["Tempo que faltava para<br/>o critério da idade. Em Dias", "{$this->diasIdadeQueFalta} dias"],
                ["Tempo que faltava para<br/>o critério da idade. Em Meses: ({$this->diasIdadeQueFalta} / 30)", "Real -> " . ($this->diasIdadeQueFalta / 30) . " meses<br/>Arredondado -> {$this->mesesIdadeQueFalta} meses"],
                ["Nova data do critário idade com o redutor", $this->dataCriterioRedutor]
            ];

            # Tabela Tempo até 31/12/2021
            if ($relatorio) {
                tituloRelatorio("Calculo do Redutor");
                $tabela = new Relatorio();
                $tabela->set_cabecalhoRelatorio(false);
                $tabela->set_menuRelatorio(false);
                $tabela->set_totalRegistro(false);
                $tabela->set_dataImpressao(false);
                $tabela->set_bordaInterna(true);
                $tabela->set_log(false);
            } else {
                $tabela = new Tabela();
                $tabela->set_titulo("Calculo do Redutor");

                $tabela->set_formatacaoCondicional(array(
                    array('coluna' => 1,
                        'operador' => '=',
                        'valor' => $this->dataCriterioRedutor,
                        'id' => 'vigente')));
            }

            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Valor"]);
            $tabela->set_width([70, 30]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            #$tabela->set_colunaSomatorio(1);
            $tabela->show();
        } else {
            if ($relatorio) {
                tituloRelatorio("Calculo do Redutor");
            } else {
                tituloTable("Calculo do Redutor");
            }
            $painel = new Callout();
            $painel->abre();
            p($this->analiseReducao, "center");
            $painel->fecha();
        }
    }

    ###########################################################

    public function exibe_tabelaCalculoRedutorDetalhado($relatorio = false) {

        # Verifica se cabe o redutor
        if (dataMaior($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade) == $this->dataCriterioIdade) {

            # Tabela de Simone
            $tempoInicial = $this->regraContribuicao;
            $contadorIdade = $this->regraIdade;
            $contadorIdadeMeses = 0;
            $contadorGeral = 0;
            $dataIdade = $this->dataCriterioIdade;
            $dataContribuicao = $this->dataCriterioTempoContribuicao;

            $contadorMesesFaltam = $this->mesesParaPagar;

            for ($a = $tempoInicial; $a < 50; $a++) {
                for ($b = 0; $b < 12; $b++) {
                    $array1[] = [
                        $contadorGeral,
                        $dataContribuicao,
                        "{$a} anos e {$b} meses",
                        $contadorMesesFaltam,
                        "{$contadorIdade} anos e {$contadorIdadeMeses} meses",
                        $dataIdade];

                    if ($contadorIdadeMeses == 0) {
                        $contadorIdadeMeses = 11;
                        $contadorIdade--;
                    } else {
                        $contadorIdadeMeses--;
                    }

                    if ($contadorGeral > $this->mesesParaPagar) {
                        break;
                    } else {
                        $contadorGeral++;
                    }

                    $dataIdade = addMeses($this->dataCriterioIdade, -$contadorGeral);
                    #$dataContribuicao = addDias($dataContribuicao, 30, false);
                    $dataContribuicao = addMeses($this->dataCriterioTempoContribuicao, $contadorGeral);

                    $contadorMesesFaltam--;
                }

                if ($contadorGeral > $this->mesesParaPagar) {
                    break;
                }
            }

            # Exibe a tabela
            if ($relatorio) {
                tituloRelatorio("Tabela de Redução Idade");
                $tabela = new Relatorio();
                $tabela->set_cabecalhoRelatorio(false);
                $tabela->set_menuRelatorio(false);
                $tabela->set_totalRegistro(false);
                $tabela->set_dataImpressao(false);
                $tabela->set_bordaInterna(true);
                $tabela->set_log(false);
            } else {
                $tabela = new Tabela();
                $tabela->set_titulo("Tabela de Redução Idade");
            }

            $tabela->set_conteudo($array1);
            $tabela->set_label(["Meses<br/>para Pagar", "Data do <br/>Tempo de Contribuição ", "Tempo de Contribuição", "Meses<br/>que Faltam", "Idade do Servidor", "Redução da<br/>Data Idade"]);
            $tabela->set_width([10, 20, 25, 10, 25, 20]);
            $tabela->set_totalRegistro(false);

            if (!$relatorio) {
                $tabela->set_formatacaoCondicional(array(
                    array('coluna' => 0,
                        'operador' => '=',
                        'valor' => $this->mesesParaPagar,
                        'id' => 'vigente')));
            }
            $tabela->show();
        }
    }

    ###########################################################

    public function exibe_tabelaCalculoRedutorDetalhado2($relatorio = false) {

        # Verifica se cabe o redutor
        if (dataMaior($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade) == $this->dataCriterioIdade) {

            # Tabela
            $contadorGeral = 0;
            $mesesFaltam = $this->mesesIdadeQueFalta;

            # Contadores
            $contadorContribuicao = 0;
            $contadorIdade = 0;

            # Datas
            $dataContribuicao = $this->dataCriterioTempoContribuicao;
            $dataIdade = $this->dataIdadeAposContribuicao;

            for ($a = 0; $a < (($this->mesesIdadeQueFalta / 2) + 3); $a++) {
                $array1[] = [
                    month($dataContribuicao) . " / " . year($dataContribuicao),
                    $contadorGeral,
                    "Contribuição",
                    $dataContribuicao,
                    $mesesFaltam
                ];

                $mesesFaltam--;
                $contadorGeral++;
                $contadorContribuicao++;

                $array1[] = [
                    month($dataIdade) . " / " . year($dataIdade),
                    $contadorGeral,
                    "Idade",
                    $dataIdade,
                    $mesesFaltam
                ];

                $mesesFaltam--;
                $contadorGeral++;
                $contadorIdade++;

                # Incrementa as datas
                $dataContribuicao = addMeses($this->dataCriterioTempoContribuicao, $contadorContribuicao);
                $dataIdade = addMeses($this->dataIdadeAposContribuicao, $contadorIdade);
            }

            # Exibe a tabela
            if ($relatorio) {
                tituloRelatorio("Tabela de Redução Idade");
                $tabela = new Relatorio();
                $tabela->set_cabecalhoRelatorio(false);
                $tabela->set_menuRelatorio(false);
                $tabela->set_totalRegistro(false);
                $tabela->set_dataImpressao(false);
                $tabela->set_bordaInterna(true);
                $tabela->set_log(false);
            } else {
                $tabela = new Tabela();
                $tabela->set_titulo("Tabela de Redução Idade");
                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
            }

            $tabela->set_conteudo($array1);
            $tabela->set_label(["Mes", "Meses<br/>Pagos", "Tipo", "Data", "Meses<br/>para Pagar"]);
            $tabela->set_width([10, 10, 20, 20, 10, 10]);
            #$tabela->set_align(["center", "center", "right"]);
            $tabela->set_totalRegistro(false);

            if (!$relatorio) {
                $tabela->set_formatacaoCondicional(array(
                    array('coluna' => 4,
                        'operador' => '=',
                        'valor' => 0,
                        'id' => 'vigente')));
            }
            $tabela->show();
        }
    }

    ###########################################################

    public function exibe_telaServidor($idServidor, $idUsuario) {

        # Faz a análise
        $this->fazAnalise($idServidor);

        # Grava no log a atividade
        $intra = new Intra();
        $atividade = "Cadastro do servidor - Aposentadoria - {$this->tipo}<br/>{$this->get_descricao()}";
        $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidor);

        $grid1 = new Grid();
        $grid1->abreColuna(12);

        # Exibe a regra
        tituloTable("{$this->tipo}<br/>{$this->descricao}", null, $this->legislacao);
        $this->exibe_analise();

        $grid1->fechaColuna();
        $grid1->abreColuna(12, 12, 8);

        $this->exibe_tabelaDados();

        # Exibe afastamentos que interrompem o tempo de serviço
        $aposentadoria = new Aposentadoria();
        if ($this->afastementosEspecificos AND $aposentadoria->get_semTempoServico($idServidor) > 0) {
            $this->exibe_tabelaAfastamentosSemtempoServico($idServidor);
        }

        # Pedágio
        if (!empty($this->pedagio)) {
            $gridPedagio = new Grid();
            $gridPedagio->abreColuna(12, 6);

            $this->exibe_tempoAntesDataAlvo();

            $gridPedagio->fechaColuna();
            $gridPedagio->abreColuna(12, 6);

            $this->exibe_calculoPedagio();

            $gridPedagio->fechaColuna();
            $gridPedagio->fechaGrid();
        }

        # Pontos
        if (!empty($this->pontosHomem)) {
            $this->exibe_tabelaHistoricoPontuacao();
        }

        # Redutor
        if ($this->temRedutor) {
            $this->exibe_tabelaCalculoRedutor();
            $this->exibe_tabelaCalculoRedutorDetalhado2();
        }

        # Cartilha
        $this->exibe_cartilha();

        $grid1->fechaColuna();
        $grid1->abreColuna(12, 12, 4);

        # Remuneração
        $this->exibe_tabelaRemuneração();

        # Abono Permanência
        if (!$this->permiteAbonoPermanencia) {
            callout("Essa regra <b>NÃO</b> concede Abono Permanência!","warning","center");
        }

        # Regras Gerais
        $this->exibe_tabelaRegras();
        if (!empty($this->pontosHomem)) {
            $this->exibe_tabelaRegrasPontos();
        }

        $grid1->fechaColuna();
        $grid1->fechaGrid();
    }

    ###########################################################

    public function exibe_relatorio($idServidor, $idUsuario) {
        # Faz a análise
        $this->fazAnalise($idServidor);

        # Grava no log a atividade
        $intra = new Intra();
        $atividade = "Visualizou o relatório de Aposentadoria - Regras Permanentes<br/>{$this->get_descricao()}";
        $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidor);

        # Dados do Servidor
        Grh::listaDadosServidorRelatorio2(
                $idServidor,
                "{$this->get_tipo()}<br/>{$this->get_descricao()}",
                $this->get_legislacao(),
                true,
                $this->mensagemRelatorio
        );
        br();

        # Relatório
        p($this->exibe_analiseRelatorio(), "center");

        # Dados Gerais
        $this->exibe_tabelaDados(true);

        # Exibe afastamentos que interrompem o tempo de serviço
        $aposentadoria = new Aposentadoria();
        if ($this->afastementosEspecificos AND $aposentadoria->get_semTempoServico($idServidor) > 0) {
            $this->exibe_tabelaAfastamentosSemtempoServico($idServidor, true);
        }

        # Pedágio
        if (!empty($this->pedagio)) {
            $gridPedagio = new Grid();
            $gridPedagio->abreColuna(12, 6);

            $this->exibe_tempoAntesDataAlvo(true);

            $gridPedagio->fechaColuna();
            $gridPedagio->abreColuna(12, 6);

            $this->exibe_calculoPedagio(true);

            $gridPedagio->fechaColuna();
            $gridPedagio->fechaGrid();
        }

        # Histórico dos Pontos
        if (!empty($this->pontosHomem)) {
            $this->exibe_tabelaHistoricoPontuacao(true);
        }

        # Redutor
        if ($this->temRedutor) {
            $this->exibe_tabelaCalculoRedutor(true);
        }

        # Remuneração
        $this->exibe_tabelaRemuneração(true);

        # Regras Gerais
        $this->exibe_tabelaRegras(true);

        # Regras Pontos
        if (!empty($this->pontosHomem)) {
            $this->exibe_tabelaRegrasPontos(true);
        }
    }

    ###########################################################

    public function exibe_tabelaAfastamentosSemTempoServico($idServidor, $relatorio = false) {

        if ($relatorio) {
            tituloRelatorio("Afastamentos Que interrompem o Tempo de Serviço");
        } else {
            titulotable("Afastamentos Que interrompem o Tempo de Serviço");

            $painel1 = new Callout();
            $painel1->abre();
        }

        $aposentadoria = new Aposentadoria();

        # Exibe os afastamentos que interrompem o tempo de contribuição
        if ($aposentadoria->get_semTempoServicoSemTempoContribuicao($idServidor) > 0) {
            $afast1 = new ListaAfastamentosServidor($idServidor, "SEM Contribuição");
            $afast1->set_semTempoServicoSemTempoContribuicao(true);
            $afast1->set_semTempoServicoComTempoContribuicao(false);
            if ($relatorio) {
                $afast1->exibeRelatorio();
            } else {
                $afast1->exibeTabela();
            }
        }

        # Exibe os afastamentos que interrompem o tempo de serviço
        if ($aposentadoria->get_semTempoServicoComTempoContribuicao($idServidor) > 0) {
            $afast2 = new ListaAfastamentosServidor($idServidor, "COM Contribuição");
            $afast2->set_semTempoServicoSemTempoContribuicao(true);
            $afast2->set_semTempoServicoComTempoContribuicao(true);
            if ($relatorio) {
                $afast2->exibeRelatorio();
            } else {
                $afast2->exibeTabela();
            }
        }

        if (!$relatorio) {
            $painel1->fecha();
        }
    }

    ###########################################################
}
