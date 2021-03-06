<?php

class AcumulacaoDeclaracao {

    /**
     * Abriga as várias rotina referentes a entrega da declaração de acumulação de cargos públicos de um servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function getNumDecEntregues($ano = null, $idLotacao = null, $parametroNome = null) {

        /**
         * Informa o número de declarações entregues em um determinado ano de referência
         * 
         * @param $ano integer null O anod e referência. Caso o ano não for informado, o ano vigente será considerado.
         * 
         * @syntax $AcumulacaoDeclaracao->getNumDecEntregues([$ano]);
         */

        # slq
        $select = "SELECT COUNT(idAcumulacaoDeclaracao)
                     FROM tbacumulacaodeclaracao LEFT JOIN tbservidor USING (idServidor)
                                             LEFT JOIN tbpessoa USING (idPessoa)
                                             LEFT JOIN tbhistlot USING (idServidor)
                                             LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbacumulacaodeclaracao.idServidor)
                      AND anoReferencia = '{$ano}'";

        # nome
        if (!empty($parametroNome)) {
            $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
        }

        # lotacao
        if (!empty($idLotacao) AND $idLotacao <> "*") {
            # Verifica se o que veio é numérico
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = '{$idLotacao}')";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }
        $pessoal = new Pessoal();
        $num = $pessoal->select($select, false);
        return $num[0];
    }

##############################################################

    public function getNumAcumula($ano = null, $idLotacao = null, $parametroNome = null) {

        /**
         * Informa o número de declarações que acumulam eum um determinado ano de referência
         * 
         * @param $ano integer null O anod e referência. Caso o ano não for informado, o ano vigente será considerado.
         * 
         * @syntax $AcumulacaoDeclaracao->getNumDecEntregues([$ano]);
         */

        # slq
        $select = "SELECT COUNT(idAcumulacaoDeclaracao)
                     FROM tbacumulacaodeclaracao LEFT JOIN tbservidor USING (idServidor)
                                             LEFT JOIN tbpessoa USING (idPessoa)
                                             LEFT JOIN tbhistlot USING (idServidor)
                                             LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbacumulacaodeclaracao.idServidor)
                      AND acumula
                      AND anoReferencia = '{$ano}'";

        # nome
        if (!empty($parametroNome)) {
            $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
        }

        # lotacao
        if (!empty($idLotacao) AND $idLotacao <> "*") {
            # Verifica se o que veio é numérico
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = '{$idLotacao}')";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }
        $pessoal = new Pessoal();
        $num = $pessoal->select($select, false);
        return $num[0];
    }

##############################################################

    public function showResumoGeral($ano = null, $idLotacao = null, $parametroNome = null) {

        /**
         * Informa os totais de servidores do setor com ou sem entrega
         * 
         * @param $ano integer null O anod e referência. Caso o ano não for informado, o ano vigente será considerado.
         * 
         * @syntax $AcumulacaoDeclaracao->showResumoGeral();  
         */

        $entregaram = $this->getNumDecEntregues($ano, $idLotacao, $parametroNome);
        $servidores = $this->getnumServidoresAtivos($idLotacao, $parametroNome);

        $array[] = array("Entregaram", $entregaram);
        $array[] = array("NÃO Entregaram", $servidores - $entregaram);

        if (!empty($idLotacao) AND $idLotacao <> "*") {
            $pessoal = new Pessoal();
            $titulo = $pessoal->get_nomeLotacao($idLotacao)." ({$ano}}";
        } else {
            $titulo = "Resumo Geral ({$ano}}";
        }

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Descrição", "Nº de Servidores"));
        $tabela->set_totalRegistro(false);
        $tabela->set_align(array("center"));
        $tabela->set_titulo($titulo);
        $tabela->set_rodape("Total de Servidores: " . $servidores);
        $tabela->show();
    }

    ###########################################################

    public function showResumoAcumula($ano = null, $idLotacao = null, $parametroNome = null) {

        /**
         * Informa os totais de servidores do setor com ou sem entrega
         * 
         * @param $ano integer null O anod e referência. Caso o ano não for informado, o ano vigente será considerado.
         * 
         * @syntax $AcumulacaoDeclaracao->showResumoGeral();  
         */
        
        $acumulam = $this->getNumAcumula($ano, $idLotacao, $parametroNome);
        $entregaram = $this->getNumDecEntregues($ano, $idLotacao, $parametroNome);
        $pessoal = new Pessoal();

        $array[] = array("Acumulam", $acumulam);
        $array[] = array("NÃO Acumulam", $entregaram - $acumulam);

        if (!empty($idLotacao) AND $idLotacao <> "*") {
            $titulo = "Declaração - " . $pessoal->get_nomeLotacao($idLotacao)."  ({$ano}}";
        } else {
            $titulo = "Declaração ({$ano}}";
        }

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Descrição", "Nº de Servidores"));
        $tabela->set_totalRegistro(false);
        $tabela->set_align(array("center"));
        $tabela->set_titulo($titulo);
        $tabela->set_rodape("Total que Entregaram: " . $entregaram);
        $tabela->show();
    }

    ###########################################################

    public function getProximoAnoReferencia($idServidor = null) {

        # Verifica o $idServidor
        if (empty($idServidor)) {
            return null;
        } else {
            $select = "SELECT MAX(anoReferencia) as dd FROM tbacumulacaodeclaracao WHERE idServidor = {$idServidor} LIMIT 1";
            $pessoal = new Pessoal();
            $anoref = $pessoal->select($select, false);
            
            if (empty($anoref[0])) {
                return $this->getUltimoAnoDeclaracao();
            } else {
                return $anoref[0] + 1;
            }
        }
    }

    ###########################################################

    function getnumServidoresAtivos($idLotacao = null, $parametroNome = null) {

        /**
         * informa o número de Servidores Ativos
         * 
         * @param integer $idPessoa do servidor
         */
        $select = 'SELECT idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                             LEFT JOIN tbhistlot USING (idServidor)
                                             LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND situacao = 1';
        # nome
        if (!empty($parametroNome)) {
            $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
        }

        # Lotação
        if ((!is_null($idLotacao)) and ($idLotacao <> "*")) {
            if (is_numeric($idLotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $idLotacao . '")';
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $idLotacao . '")';
            }
        }

        $pessoal = new Pessoal();
        $count = $pessoal->count($select);
        return $count;
    }

    ###########################################################     

    /**
     * Método get_ultimoAnoDeclaracao
     * informa ultimo ano cadastrado de uma declaração
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function getUltimoAnoDeclaracao() {

        $select = "SELECT MAX(anoReferencia) as dd FROM tbacumulacaodeclaracao LIMIT 1";
        $pessoal = new Pessoal();
        $ano = $pessoal->select($select, false);

        if (empty($ano[0])) {
            return date("Y");
        } else {
           return $ano[0];
        }
    }

    ###########################################################
}
