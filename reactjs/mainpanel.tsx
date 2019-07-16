import * as React from 'react';
import { useRouterHistory } from 'react-router';
import Suggessions from './suggessions'
import ExpressionBuilder from './expressionbuilder';
import adminstore from '../../../store/admin/adminstore';
import actionTypes from '../../../actions/base/actiontypes';
import adminactioncreator from '../../../actions/administator/adminactioncreator';
// import ModalPopupActionCreator from '../../../actions/utility/modalpopupaction';
import DepencydencyDimensions from './dependencydimensions';
import {
    ModalPopupActionCreator, adminStore, commonHelper
} from '../../../components/dashboard/information/informationbase';
interface iProps {
    targetId?: any;
    targetData?: any;
    targetParameterAction?: any;
    tenures?: any;
    parameterTargetId?: any;
    clearTargetParameter?: any;
    changeActiveTarget?: Function;
    getTargetParameters?: Function;
    selectTarget?: Function;
    isOverallTargetExist?: any;
    isTargetTabActive?: any;
    showBuilder?: any;
    currentTargetsWeightage?: any;
    selectTargetafterCreateUpdate?: Function;
    dashboardOrders?: any;
}

export default class TargetsAndSuggestions extends React.Component<iProps, any> {

    constructor(props: any) {
        super();
        // let isAbsolute = props.targetData.isAbsolute;
        // if (props.targetData.type == '1') {
        //     //growth
        //     isAbsolute = false;
        // }
        // else if (props.targetData.type == '0') {
        //     //parameter value
        //     isAbsolute = true;
        // }
        console.log(adminstore.seriesRules)
        this.state = {
            tabIndex: '1',
            selectedTargetData: props.targetData,
            name: props.targetData.name,
            value: props.targetData.value,
            cutInValue: props.targetData.cutInValue,
            weightage: props.targetData.weightage,
            unit: props.targetData.unit.length === 0 ? this.getCurrencyUnitInSeries() : props.targetData.unit,
            type: props.targetData.type === '' ? 2 : props.targetData.type,
            dashboardOrder: props.targetData.dashboardOrder,
            budgetOrder: props.targetData.budgetOrder,
            tenureValue: props.targetData.tenureValue,
            tenureId: props.targetData.tenureId == '' ? props.tenures[0].id : props.targetData.tenureId,
            budgetCode: props.targetData.budgetCode,

            isBudgetable: props.targetData.isBudgetable,
            isApplicableForBudgetValidation: props.targetData.isApplicableForBudgetValidation,

            //isAbsolute: isAbsolute,
            showOnDashboard: props.targetData.showOnDashboard,
            useAsReferenceForBudgetPrepopulation: props.targetData.useAsReferenceForBudgetPrepopulation,

            // showBuilder: props.showBuilder,
            tenures: props.tenures,
            isValid: true,
            expression: '',
            dependencies: [],
            displayDependencies: [],
            displayExpression: '',
            openDimensionPop: false,
            isForDisplay: false,
            expressionMetadata: '',
            displayExpressionMetadata: '',
            isTypeGrowth: props.targetData.type == '1' ? true : false,
            isTypePvalue: props.targetData.type == '0' ? true : false,
            isTargetTabActive: props.isTargetTabActive,
            disableSaveBtn: true,
            showTenure: false,
            maxAllowedWeightage: (props.targetParameterAction == 'create') ? 100 - parseFloat(props.currentTargetsWeightage) : (100 - parseFloat(props.currentTargetsWeightage)) + parseFloat(props.targetData.weightage),
            isWeightageExceeded:false,
            dashboardOrders:props.dashboardOrders,
            isDashboardOrderExist:false,
            currentDashboardOrder:props.targetData.dashboardOrder,
            parameterTargetDependency:props.targetData.parameterTargetDependency,
        }
        //alert(parseFloat(props.currentTargetsWeightage));
        //alert(parseFloat(100)-parseFloat(props.currentTargetsWeightage));
    }

    componentWillReceiveProps(nextProps: any) {
        //alert(nextProps.targetData.dashboardOrder);
        // let isAbsolute = nextProps.targetData.isAbsolute;
        // if (nextProps.targetData.type == '1') {
        //     //growth
        //     isAbsolute = false;
        // }
        // else if (nextProps.targetData.type == '0') {
        //     //parameter value
        //     isAbsolute = true;
        // }
        this.setState({
            selectedTargetData: nextProps.targetData,
            name: nextProps.targetData.name,
            value: nextProps.targetData.value,
            cutInValue: nextProps.targetData.cutInValue,
            weightage: nextProps.targetData.weightage,
            unit: !nextProps.targetData.id && nextProps.targetData.unit.length === 0 ? this.getCurrencyUnitInSeries() : nextProps.targetData.unit,
            type: nextProps.targetData.type === '' ? 2 : nextProps.targetData.type,
            dashboardOrder: nextProps.targetData.dashboardOrder,
            budgetOrder: nextProps.targetData.budgetOrder,
            tenureValue: nextProps.targetData.tenureValue,
            budgetCode: nextProps.targetData.budgetCode,
            tenureId: nextProps.targetData.tenureId == '' ? nextProps.tenures[0].id : nextProps.targetData.tenureId,
            isBudgetable: nextProps.targetData.isBudgetable,
            isApplicableForBudgetValidation: nextProps.targetData.isApplicableForBudgetValidation,
            //isAbsolute: isAbsolute,
            showOnDashboard: nextProps.targetData.showOnDashboard,
            useAsReferenceForBudgetPrepopulation: nextProps.targetData.useAsReferenceForBudgetPrepopulation,
            expression: nextProps.targetData.expression,
            displayExpression: nextProps.targetData.displayExpression,
            expressionMetadata: nextProps.targetData.expressionMetadata,
            displayExpressionMetadata: nextProps.targetData.displayExpressionMetadata,
            isTypeGrowth: nextProps.targetData.type == '1' ? true : false,
            isTypePvalue: nextProps.targetData.type == '0' ? true : false,
            dependencies: nextProps.targetData.dependencies,
            displayDependencies: nextProps.targetData.displayDependencies,
            isTargetTabActive: nextProps.isTargetTabActive,
            tabIndex: nextProps.isTargetTabActive ? '1' : this.state.tabIndex,
            // showBuilder: nextProps.showBuilder,
            disableSaveBtn: true,
            maxAllowedWeightage: (nextProps.targetParameterAction == 'create') ? 100 - parseFloat(nextProps.currentTargetsWeightage) : (100 - parseFloat(nextProps.currentTargetsWeightage)) + parseFloat(nextProps.targetData.weightage),
            isValid: true,
            isWeightageExceeded:false,
            dashboardOrders:nextProps.dashboardOrders,
            isDashboardOrderExist:false,
            currentDashboardOrder:nextProps.targetData.dashboardOrder,
            parameterTargetDependency:nextProps.targetData.parameterTargetDependency,
        });
        let tenureId = nextProps.targetData.tenureId == '' ? nextProps.tenures[0].id : nextProps.targetData.tenureId;
        this.showHideTenureValue(tenureId);
    }
    private _editObj: any = {};
    componentDidMount() {
        adminstore.addChangeListener(actionTypes.UPDATE_TARGET_PARAMETER, this.updateTargetParameterCallback);
        adminstore.addChangeListener(actionTypes.AUTO_UPDATE_TARGET_PARAMETER, this.autoupdateTargetParameterCallback);

        adminstore.addChangeListener(actionTypes.CREATE_TARGET_PARAMETER, this.createTargetParameterCallback);
        adminstore.addChangeListener(actionTypes.DELETE_TARGET_PARAMETER, this.deleteTargetParameterCallback);
    }

    componentWillUnmount() {
        adminstore.removeChangeListener(actionTypes.UPDATE_TARGET_PARAMETER, this.updateTargetParameterCallback);
        adminstore.removeChangeListener(actionTypes.AUTO_UPDATE_TARGET_PARAMETER, this.autoupdateTargetParameterCallback);
        adminstore.removeChangeListener(actionTypes.CREATE_TARGET_PARAMETER, this.createTargetParameterCallback);
        adminstore.removeChangeListener(actionTypes.DELETE_TARGET_PARAMETER, this.deleteTargetParameterCallback);
    }


    private getCurrencyUnitInSeries = () => {
        let currencyUnit: any = '';
        for (let i = 0; i < adminStore.seriesRules.length; i++) {
            if (adminStore.seriesRules[i].name == 'CurrencyUnit') {
                currencyUnit = adminStore.seriesRules[i].value;
                break;
            }
        }
        return currencyUnit;
    }

    //LOCAL FUNCTION
    private showHideTenureValue = (tenureId: any) => {
        let selectedTenure = this.props.tenures.filter((item: any) => {
            return item.id == tenureId
        })

        if (selectedTenure.length && (selectedTenure[0].type == 3 || selectedTenure[0].type == 2 || selectedTenure[0].type == 4)) {
            this.setState({ showTenure: true });
        }
        else {
            this.setState({ showTenure: false,tenureValue:'' });
        }
    }

    private setTabIndex = (tabId: any) => {
        this.setState({ tabIndex: tabId });
    }

    private changeCutInValue = (event: any) => {
        this.setState({ cutInValue: event.target.value, disableSaveBtn: false });
    }

    private changeName = (event: any) => {
        this.setState({ name: event.target.value, disableSaveBtn: false })
    }

    private changeValue = (event: any) => {
        this.setState({ value: event.target.value, disableSaveBtn: false })
    }

    private changeWeightage = (event: any) => {

        this.setState({ weightage: event.target.value, disableSaveBtn: false })
        let isWeightageExceeded = false;
        if (event.target.value === '' || event.target.value <= 0 || event.target.value > this.state.maxAllowedWeightage) {
            //if (event.target.value != '' && event.target.value > this.state.maxAllowedWeightage) {
            isWeightageExceeded = true;

        }
        this.setState({
            isWeightageExceeded: isWeightageExceeded
        })
    }

    private changeUnit = (event: any) => {
        this.setState({ unit: event.target.value, disableSaveBtn: false })
    }

    private changeType = (event: any) => {
        this.setState({
            type: event.target.value,
            expression: '',
            displayExpression: '',
            displayExpressionMetadata:'',
            expressionMetadata:'',
            dependencies: [],
            displayDependencies: [],
            isTypeGrowth: event.target.value == '1' ? true : false,
            isTypePvalue: event.target.value == '0' ? true : false,
            disableSaveBtn: false
        })
    }
    private validateDashboardOrder = (dashboardOrder: any) => {

        this.setState({ isDashboardOrderExist: false })
        let inValidDashboardOrder = false;
        //if (this.state.showOnDashboard) {

            if ((dashboardOrder === ''||dashboardOrder ==null || dashboardOrder <= 0 || this.invalidNumber(dashboardOrder))) {
                inValidDashboardOrder = true;
                this.setState({ isDashboardOrderExist: true })
            }
            else if (this.state.dashboardOrders.length) {
                this.state.dashboardOrders.map((value: any, index: any) => {
                    
                    if (value == dashboardOrder) {
                        this.setState({ isDashboardOrderExist: true })
                        inValidDashboardOrder = true;
                    }

                });
            }
        //}
        return inValidDashboardOrder;

    }
    private changeDashboardOrder = (event: any) => {
        this.setState({ dashboardOrder: event.target.value, disableSaveBtn: false })
        this.validateDashboardOrder(event.target.value);
    }

    private changeBudgetOrder = (event: any) => {
        this.setState({ budgetOrder: event.target.value, disableSaveBtn: false })
    }

    private changeTenureValue = (event: any) => {
        this.setState({ tenureValue: event.target.value, disableSaveBtn: false })
    }

    // private changeIsBudgetable = () => {
    //     let isBudgetable = !this.state.isBudgetable;

    //     this.setState({ isBudgetable: isBudgetable })
    // }

    // private changeIsAbsolute = () => {

    //     let isAbsolute = !this.state.isAbsolute;

    //     this.setState({ isAbsolute: isAbsolute, disableSaveBtn: false })
    // }

    private changeBudgetPrepopulation = () => {
        let useAsReferenceForBudgetPrepopulation = !this.state.useAsReferenceForBudgetPrepopulation;

        this.setState({ useAsReferenceForBudgetPrepopulation: useAsReferenceForBudgetPrepopulation, disableSaveBtn: false })
        this._editObj = {};
        if (this.props.targetData.useAsReferenceForBudgetPrepopulation != useAsReferenceForBudgetPrepopulation) {
            //for update purpose only
            this._editObj = { useAsReferenceForBudgetPrepopulation: useAsReferenceForBudgetPrepopulation };
        }

    }

    private changeShowOnDashboard = () => {
        let showOnDashboard = !this.state.showOnDashboard;
        this.setState({ showOnDashboard: showOnDashboard, disableSaveBtn: false })
    }
    private changeBudgetValidation = () => {
        let isApplicableForBudgetValidation = !this.state.isApplicableForBudgetValidation;
        this.setState({ isApplicableForBudgetValidation: isApplicableForBudgetValidation, disableSaveBtn: false })
        if(isApplicableForBudgetValidation)
        {
            this.setTenure();
        }
        //showHideTenureValue
    }
    private setTenure=()=>{
        //0:Every Year,2:Specific year
        let everyYearTenureId:any;
        this.props.tenures.map((item: any) => {
            if(item.type==0)
            {
                everyYearTenureId=item.id;
            }
        });
        this.props.tenures.map((item: any) => {
            
            if(item.id == this.state.tenureId)
            {
                if(item.type!=0 && item.type!=2)
                {
                    this.setState({
                        tenureId:everyYearTenureId
                    });
                    this.showHideTenureValue(everyYearTenureId);
                }
            }
            //return item.id == tenureId
        })

        // if (selectedTenure.length && (selectedTenure[0].type == 3 || selectedTenure[0].type == 2 || selectedTenure[0].type == 4)) {
        //     this.setState({ showTenure: true });
        // }
        // else {
        //     this.setState({ showTenure: false });
        // }
    }
    private openExpressionBuilder = (isForDisplay: boolean) => {
        this.setState({
            showBuilder: !this.state.showBuilder,
            isForDisplay: isForDisplay
        })
    }

    // private changeBudgetCode = (event: any) => {
    //     this.setState({
    //         budgetCode: event.target.value,
    //         disableSaveBtn:false
    //     })

    // }

    private changeTenureId = (event: any) => {
        this.setState({ tenureId: event.target.value, disableSaveBtn: false })
        this.showHideTenureValue(event.target.value);
    }

    //END OF LOCAL FUNCTIONS
    private createTargetParameter = () => {
        let dependencies = this.state.dependencies;
        this.state.displayDependencies.map((value: any) => {
            dependencies.push(value);
        })
        // let isAbsolute = this.state.isAbsolute;
        // if (this.state.type == '1') {
        //     //growth
        //     isAbsolute = false;
        // }
        // else if (this.state.type == '0') {
        //     //parameter value
        //     isAbsolute = true;
        // }
        let data: any = [{
            name: this.state.name,
            value: this.state.value,
            cutInValue: this.state.cutInValue,
            weightage: this.state.weightage,
            unit: this.state.unit,
            type: this.state.type,
            dashboardOrder: this.state.dashboardOrder,
            budgetOrder: this.state.budgetOrder,
            tenureId: this.state.tenureId,
            tenureValue: this.state.tenureValue,
            budgetCode: this.state.budgetCode,
            //expression: '(DCCC)',
            expression: this.state.expression,
            displayExpression: this.state.displayExpression,
            isBudgetable: this.state.isApplicableForBudgetValidation,
            isAbsolute: false,
            useAsReferenceForBudgetPrepopulation: this.state.useAsReferenceForBudgetPrepopulation,
            showOnDashboard: this.state.showOnDashboard,
            isApplicableForBudgetValidation: this.state.isApplicableForBudgetValidation,
            dependencies: this.state.dependencies,
            expressionMetadata: this.state.expressionMetadata,
            displayExpressionMetadata: this.state.displayExpressionMetadata
            // dependencies:[{
            //     code: "FSL1COCO",
            //     seriesParameterId: "b430dd1d-e7f6-4b45-83cd-7b729ea23b1e",
            //     tenureReference: 0,
            //     tenureReferenceType: 1,
            //     isForDisplay: true
            // },
            // {
            //     code: "FSL1COC1",
            //     seriesParameterId: "bdd46bbd-27af-4a60-9b6c-9b55cfb208e7",
            //     tenureReference: 0,
            //     tenureReferenceType: 1,
            //     isForDisplay: true
            // }]
        }]
        console.log(this.state.dependencies);
        adminactioncreator.createTargetParameter(this.props.targetId, data);
    }

    private updateTargetParameter = () => {
        // let isAbsolute = this.state.isAbsolute;
        // if (this.state.type == '1') {
        //     //growth
        //     isAbsolute = false;
        // }
        // else if (this.state.type == '0') {
        //     //parameter value
        //     isAbsolute = true;
        // }
        let data: any = {
            name: this.state.name,
            value: this.state.value,
            cutInValue: this.state.cutInValue,
            weightage: this.state.weightage,
            unit: this.state.unit,
            type: this.state.type,
            dashboardOrder: this.state.dashboardOrder,
            budgetOrder: this.state.budgetOrder,
            tenureId: this.state.tenureId,
            tenureValue: this.state.tenureValue,
            budgetCode: this.state.budgetCode,
            expression: this.state.expression,
            displayExpression: this.state.displayExpression,
            isBudgetable: this.state.isApplicableForBudgetValidation,
            //isAbsolute: isAbsolute,
            //useAsReferenceForBudgetPrepopulation: this.state.useAsReferenceForBudgetPrepopulation,
            showOnDashboard: this.state.showOnDashboard,
            isApplicableForBudgetValidation: this.state.isApplicableForBudgetValidation,
            expressionMetadata: this.state.expressionMetadata,
            displayExpressionMetadata: this.state.displayExpressionMetadata

        }
        if ('useAsReferenceForBudgetPrepopulation' in this._editObj) {
            data['useAsReferenceForBudgetPrepopulation'] = this._editObj.useAsReferenceForBudgetPrepopulation

        }

        adminactioncreator.updateTargetParameter(this.props.targetId, this.props.parameterTargetId, data);

    }
    private autoupdateTargetParameterCallback = (response: any) => {
        let updatedTargetParameterId = this.props.parameterTargetId;
        this.props.getTargetParameters();
        setTimeout(() => {
            this.props.selectTarget(updatedTargetParameterId);
        }, 500);
    }

    private updateTargetParameterCallback = (response: any) => {
        if (response.success) {
            let updatedTargetParameterId = this.props.parameterTargetId;
            this.props.getTargetParameters(true);
            this.setState({ disableSaveBtn: true });
            this.props.selectTargetafterCreateUpdate(updatedTargetParameterId);
            let alertData = {

                callback() {
                },
                messageText: 'Target Parameter Updated Successfully.',
                title: 'Success'
            }
            setTimeout(() => {

                ModalPopupActionCreator.openAlertBox(alertData);
            }, 500);
        }
    }
    private deleteTargetParameterCallback = (response: any) => {
        if (response.success) {

            this.props.getTargetParameters();
            this.setState({ disableSaveBtn: true });
        }
        let alertData = {
            callback() {
            },
            messageText: 'Target Parameter Deleted Successfully.',
            title: 'Success'
        }
        setTimeout(() => {
            ModalPopupActionCreator.openAlertBox(alertData);
        }, 500);
    }
    private createTargetParameterCallback = (response: any) => {
        if (response.success) {
            this.setState({ disableSaveBtn: true });
            this.props.getTargetParameters(true);
            this.props.selectTargetafterCreateUpdate(response.data[0].id);
            let alertData = {
                callback() {
                },
                messageText: 'Target Parameter Created Successfully.',
                title: 'Success'
            }
            setTimeout(() => {

                ModalPopupActionCreator.openAlertBox(alertData);

            }, 500);

        }

    }
    private getTargetParameters = () => {

        this.props.getTargetParameters();
    }
    private onSave = () => {

        let alertMsg = '';
        let isDependencyValid = true;
        let inValidDashboardOrder = this.validateDashboardOrder(this.state.dashboardOrder);
        if (this.state.name === '') {
            this.setState({
                isValid: false
            })
        }
        else if (this.state.value === '' || this.state.value <= 0) {
            this.setState({
                isValid: false
            })
        }
        else if (this.state.cutInValue === '' || this.state.cutInValue <= 0 || parseFloat(this.state.cutInValue) >= parseFloat(this.state.value)) {

            this.setState({
                isValid: false
            })
        }
        else if (this.state.weightage === '' || this.state.weightage <= 0 || this.state.weightage > this.state.maxAllowedWeightage) {
            //else if (this.state.weightage === '' || this.state.weightage <= 0) {    
            this.setState({
                isValid: false
            })
        }

        else if (this.state.type === '') {
            this.setState({
                isValid: false
            })
        }
        // else if ((this.state.dashboardOrder === '' || this.state.dashboardOrder < 0 || this.invalidNumber(this.state.dashboardOrder)) && this.state.showOnDashboard) {
        //     this.setState({
        //         isValid: false
        //     })
        // }
        else if (inValidDashboardOrder) {
            this.setState({
                isValid: false
            })
        }
        else if ((this.state.budgetOrder === ''|| this.state.budgetOrder == null || this.state.budgetOrder < 0 || this.invalidNumber(this.state.budgetOrder)) && this.state.isApplicableForBudgetValidation) {
            this.setState({
                isValid: false
            })
        }
        else if (this.state.tenureId === '') {
            this.setState({
                isValid: false
            })
        }
        else if ((this.state.tenureValue === '' || this.state.tenureValue==null || this.invalidNumber(this.state.tenureValue)||this.state.tenureValue <= 0) && this.state.showTenure) {
            this.setState({
                isValid: false
            })
        }
        // else if (this.state.budgetCode === '' && this.state.isApplicableForBudgetValidation) {
        //     this.setState({
        //         isValid: false
        //     })
        // }
        else if (this.state.expression === '') {
            this.setState({
                isValid: false
            })

        }
        else if (this.state.displayExpression === '') {
            this.setState({
                isValid: false
            })

        }
        else if (this.props.targetParameterAction === 'create' && !this.state.dependencies.length) {
            isDependencyValid = false;
            alertMsg = 'Target Dependency is required.';

        }
        else if (this.props.targetParameterAction === 'create' && !this.state.displayDependencies.length) {
            isDependencyValid = false;
            alertMsg = 'Target Dependency for display expression is required.';

        }
        else {
            if (this.props.targetParameterAction == 'update') {
                this.updateTargetParameter();
            }
            else {
                this.createTargetParameter();
            }
            this.setState({
                isValid: true
            })
        }
        if (!isDependencyValid) {
            this.setState({
                isValid: false
            })
            let alertData = {
                callback() {
                },
                messageText: alertMsg,
                title: 'Alert'
            }
            setTimeout(() => {
                ModalPopupActionCreator.openAlertBox(alertData);
            }, 500);
        }

    }
    private validateName = (text: any) => {
        //validation to avoid special characters
        var format = /[!@#$%^&*()+\=\[\]{};':"\\|,.<>\/?]+/;
        if (format.test(text)) {
            //contains special character
            return true;
        } else {
            return false;
        }
    }

    private invalidNumber = (value: any) => {
        //validation to avoid decimal number
        var format = /^\d+\.\d{0,2}$/;
        if (format.test(value)) {
            //contains decimal value
            return true;
        } else {
            return false;
        }
    }
    private deleteTargetParameter = (parameterTargetId: any, e: any) => {

        e.stopPropagation();
        let seriesTargetId = this.props.targetId;
        let Data: any = {
            callback(confirm: boolean) {
                if (confirm) {

                    adminactioncreator.deleteTargetParameter(seriesTargetId, parameterTargetId);

                }
                else {
                }
            },
            messageText: 'Are you sure you want to delete this target parameter ?',
            title: 'Confirm Deletion'
        }
        ModalPopupActionCreator.openConfirmBox(Data);

    }

    private setExpression = (expression: any, expressionMetadata: any) => {
        // console.log(expression);
        // console.log(expressionMetadata);
        this.setState({
            expression: expression,
            expressionMetadata: expressionMetadata,
            disableSaveBtn: false
        });

    }

    private setDisplayExpression = (expression: any, expressionMetadata: any) => {
        this.setState({
            displayExpression: expression,
            displayExpressionMetadata: expressionMetadata,
            disableSaveBtn: false
        })
    }

    //copy expression,dependecy and dimesnion to display expression
    private copyExpression=()=>{
       
        let displayDependencies:any=[];
        let existingDisplayDependency:any=[];
        if(this.state.displayDependencies)
        {
            existingDisplayDependency=this.state.displayDependencies;
            
        }
        else if(this.state.parameterTargetDependency && this.state.parameterTargetDependency.length)
        {
            existingDisplayDependency= this.state.parameterTargetDependency.filter((item: any) => {
                return item.isForDisplay == true;
            })
            //console.log(existingDisplayDependency);
        }
        
        if(this.state.dependencies && this.state.dependencies.length)
        {
            displayDependencies=(this.state.isTypeGrowth)?[$.extend(true, [],this.state.dependencies[0])]:$.extend(true, [], this.state.dependencies);
            
        }
        else if(this.state.parameterTargetDependency && this.state.parameterTargetDependency.length)
        {
            
            let targetDependenciesForExpression = this.state.parameterTargetDependency.filter((item: any) => {
                return item.isForDisplay == false;
            })

            displayDependencies=(this.state.isTypeGrowth)?[$.extend(true, [], targetDependenciesForExpression[0])]:$.extend(true, [], targetDependenciesForExpression);
            
        }
        let saveArr:any=[];
        
        if(displayDependencies.length)
        {
            displayDependencies.map((item: any) => {
                item.isForDisplay=true;
                delete item['createdOn'];
                delete item['id'];
                delete item['parameterTargetId'];
                let data={
                    code: item.code,
                    isForDisplay: true,
                    isTargetValue: item.isTargetValue,
                    seriesParameterId: item.seriesParameterId,
                    tenureReference: item.tenureReference,
                    tenureReferenceType: 0,
                    //dimensionValueMaps:item.dimensionValueMaps,
                }
                console.log(item);   
                if(item.dimensionValueMaps && item.dimensionValueMaps.length)
                {
                    //console.log(item.dimensionValueMaps[0]);
                    if(item.dimensionValueMaps[0].dimensionValueIds)
                    {
                        data['dimensionValueMaps']=item.dimensionValueMaps;
                    }
                    else{
                        let dimensionValueMaps:any=[];
                        item.dimensionValueMaps.map((dimensions: any) => {
                            
                            let dimensionValueIds:any=[];
                            
                            dimensions.map((item:any)=>{
                                dimensionValueIds.push(item.id);
                            })
                            //dimensionValueIds.push(item.id);
                            //console.log(dimensionValueIds);
                            dimensionValueMaps.push({dimensionValueIds:dimensionValueIds});
                            
                        });
                        data['dimensionValueMaps']=dimensionValueMaps;
                    }
                    //console.log(data);
                    //console.log(item.dimensionValueMaps[0].dimensionValueIds);
                    //data.push({dimensionValueMaps:item.dimensionValueMaps})
                    
                } 
                //console.log(data);   
                saveArr.push(data);
            })
        }
        
        if(existingDisplayDependency.length && this.state.selectedTargetData.id!='' && saveArr.length)
        {
            
            let _that = this
            let targetId=this.state.selectedTargetData.id
            let Data: any = {
                callback(confirm: boolean) {
                    if (confirm) {
                        //console.log(displayDependencies);
                        adminactioncreator.deleteMultipleTartgetDepencencies(targetId, existingDisplayDependency);
                        adminactioncreator.createTartgetDepencencies(targetId, saveArr)
                        let data:any;
                        if(_that.state.isTypeGrowth)
                        {
                            data = {
                                displayExpression:displayDependencies[0].code,
                                displayExpressionMetadata:displayDependencies[0].code+ ','
                            }
                            
                        }
                        else{
                            data = {
                                displayExpression:_that.state.expression,
                                displayExpressionMetadata:_that.state.expressionMetadata
                            }
                            
                        }
                        let autoUpdate=true;
                        adminactioncreator.updateTargetParameter(_that.props.targetId, _that.state.selectedTargetData.id, data, autoUpdate);
                        _that.setCopiedDisplayExpression(saveArr);
                        
                    }
                },
                messageText: 'Copying the expression will delete existing display expression.Are you sure do you want to proceed?',
                title: 'Confirm Copy'
            }
            ModalPopupActionCreator.openConfirmBox(Data);
            
        }
        else{
            this.setCopiedDisplayExpression(saveArr);
        }
        
        
    }
    private setCopiedDisplayExpression=(displayDependencies:any)=>{
        
        let displayExpression:any;
        let displayExpressionMetadata:any;
        if(this.state.isTypeGrowth)
        {
            displayExpression=displayDependencies[0].code;
            displayExpressionMetadata=displayDependencies[0].code+ ',';
            
        }
        else{
            displayExpression=this.state.expression;
            displayExpressionMetadata=this.state.expressionMetadata;
        }
        //console.log(displayDependencies);
        this.setState({
            displayDependencies: displayDependencies,
        });
        this.setDisplayExpression(displayExpression,displayExpressionMetadata);
    }
    private setDependencies = (dependencies: any) => {
        //console.log(dependencies);
        //debugger
        if (this.state.isForDisplay) {
            this.setState({
                displayDependencies: dependencies
            });
        }
        else {
            this.setState({
                dependencies: dependencies
            });
        }
    }
    private cancel = () => {
        ModalPopupActionCreator.closeModal()
    }
    private openDimensions = (dependency: any, index: any, dependencyArray: any) => {
        // console.log(this.state.dependencies);
        let dependencyIds: any = [];
        let dependencies = dependencyArray;
        if ((this.state.isTypeGrowth || this.state.isTypePvalue) && dependencies.length && !this.state.isForDisplay) {
            dependencies.map((value: any, index: any) => {
                dependencyIds.push(value.id);
            })

        }
        console.log("Dependency value to dependency dimenson : ", dependency);
        //alert(this.state.type);
        // this.setState({
        //     openDimensionPop: !this.state.openDimensionPop
        // })
        ModalPopupActionCreator.openModal(<DepencydencyDimensions dependencyIds={dependencyIds} popupHead={'Dimensions'} dependency={dependency} cancel={this.cancel}
            setDependencies={this.setDependencies} dependencies={dependencies} dependencyIndex={index} />, true, 'md')
    }


    render() {
        let enableCopy=false;
        if(this.state.expression!='')
        {
            enableCopy=true;
        }
        
        let weightageError = '';

        if (this.state.weightage === '') {
            weightageError = 'Enter Weightage';
        }
        else if (this.state.weightage <= 0) {
            weightageError = 'Invalid Weightage';
        }
        else if (this.state.weightage > this.state.maxAllowedWeightage) {
            weightageError = 'The total sum of Weightages of all targets should not be exceed 100.';
        }
        let dashboardOrderError = '';
        //if (this.state.showOnDashboard) {
            if (this.state.dashboardOrder === '' ||this.state.dashboardOrder==null) {
                dashboardOrderError = 'Enter Dashboard Order';
            }
            else if (this.state.dashboardOrder <= 0 || this.invalidNumber(this.state.dashboardOrder)) { 
                dashboardOrderError = 'Invalid Dashboard Order';
            }
            else if (this.state.isDashboardOrderExist) {
                dashboardOrderError = ' Entered dashboard order is already being used in another target.';
            }
        //}


        return (
            <div className="form-wraper">
                <div className="form-tabs">
                    <div className="tab-text">
                        <div className={this.state.tabIndex == '1' ? "cp text1 sub-title active" : "cp text1 sub-title"} onClick={this.setTabIndex.bind(this, 1)}>
                            <p>Targets</p>
                        </div>
                        {/* <div className={this.state.isApplicableForBudgetValidation ? (this.state.tabIndex == '2' ? "cp text1 sub-title active" : "cp text1 sub-title") : "hidden"} onClick={this.setTabIndex.bind(this, 2)}>
                            <p>Suggestions</p>
                        </div> */}
                        <div className={"hidden"}>
                            <p>Suggestions</p>
                        </div>
                    </div>

                    <div className="tab-btn">
                        <div className="btn1">
                            {this.props.targetParameterAction !== 'create' && <button className="btn btn-primary mh-5" disabled={this.props.targetParameterAction == 'create'} onClick={this.deleteTargetParameter.bind(this, this.props.parameterTargetId)}>
                                Remove
                        </button>}
                            {this.props.targetParameterAction == 'create' && <button className="btn btn-primary mh-5" onClick={this.getTargetParameters}>
                                Cancel
                        </button>}
                        </div>
                        <div className="btn1">
                            <button className="btn btn-block btn-warning" onClick={this.onSave} disabled={this.props.isOverallTargetExist == false || this.state.disableSaveBtn}>
                                {" "}
                                Save
                        </button>
                        </div>
                    </div>
                </div>
                <div className={this.state.tabIndex == '1' ? "tables-container active" : "tables-container"}>
                    <div className="data-table-container">
                        <div className="details-form">
                            <h5 className="dib text-primary fwb text-lg ml-10 title">
                                Details
                                        </h5>
                            <div className="form-data details-container">
                                <div className="form-item details-list">
                                    <div className="form-group">
                                        <label>Name</label>
                                        <input
                                            type="text"
                                            className="form-control "
                                            placeholder=""
                                            value={this.state.name}
                                            onChange={this.changeName}
                                        />
                                        <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.name === '' ? 'Enter Name' : ''}</span>

                                    </div>
                                </div>
                                <div className="form-item details-list">
                                    <div className="form-group">
                                        <label>Target Value to Achieve</label>
                                        <input
                                            type="number" step="any"
                                            className="form-control "
                                            placeholder=" "
                                            value={this.state.value}
                                            onChange={this.changeValue}
                                        />
                                        <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.value === '' ? 'Enter Target Value' : (this.state.value <= 0 ? 'Invalid Target Value' : '')}</span>
                                    </div>
                                </div>
                                {/* </div>
                            <div className="form-data"> */}
                                <div className="form-item details-list">
                                    <div className="form-group">
                                        <label>Cut-in Value</label>
                                        <input
                                            type="number"
                                            className="form-control "
                                            placeholder=" "
                                            value={this.state.cutInValue} step="any"
                                            onChange={this.changeCutInValue}
                                        />
                                        <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.cutInValue === '' ? 'Enter Cut-in Value' : (this.state.cutInValue <= 0 ? 'Invalid Cut-in Value' : (parseFloat(this.state.cutInValue) >= parseFloat(this.state.value)) ? 'Cut-in Value should be less than Target Value' : '')}</span>
                                    </div>
                                </div>
                                <div className="form-item details-list type-style">
                                    <div className="form-group">
                                        <label>Weightage (%)</label>
                                        <input step="any"
                                            type="number"
                                            className="form-control " max={this.state.maxAllowedWeightage}
                                            placeholder=" "
                                            value={this.state.weightage}
                                            onChange={this.changeWeightage}
                                        />
                                        <span className={this.state.isValid && !this.state.isWeightageExceeded ? "hidden" : "text-danger text-center"}>{weightageError}</span>
                                    </div>
                                </div>
                                {/* <div className="form-item type-style">
                                    <div className="form-group">
                                        <label>Weightage (%)</label>
                                        <input step="any"
                                            type="number"
                                            className="form-control "
                                            placeholder=" "
                                            value={this.state.weightage}
                                            onChange={this.changeWeightage}
                                        />
                                        <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{weightageError}</span>
                                    </div>
                                </div> */}
                                {/* </div>
                            <div className="form-data"> */}
                                <div className="form-item details-list">
                                    <div className="form-group">
                                        <label>Unit</label>
                                        <input
                                            type="text"
                                            className="form-control "
                                            placeholder=" "
                                            value={this.state.unit}
                                            onChange={this.changeUnit}
                                        />

                                    </div>
                                </div>
                                <div className="form-item details-list type-style">
                                    <div className="form-group">
                                        <label>Type</label>

                                        <select disabled={this.props.targetParameterAction === 'update'} className="form-control" value={this.state.type != '' ? this.state.type : ''} onChange={this.changeType}>

                                            <option value="0">Parameter Value</option>
                                            <option value="1">Growth</option>
                                            <option value="2">Expression</option>
                                        </select>
                                        <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.type === '' ? 'Select Type' : ''}</span>
                                    </div>
                                </div>
                                {/* </div>
                            <div className="form-data"> */}
                                <div className="form-item details-list">
                                    <div className="form-group">

                                        <label>Dashboard Order</label>
                                        <input
                                            type="number"
                                            className="form-control "
                                            placeholder=" "
                                            value={this.state.dashboardOrder==null?'':this.state.dashboardOrder}
                                            onChange={this.changeDashboardOrder} min="1"
                                        />
                                        <span className={this.state.isValid && !this.state.isDashboardOrderExist ? "hidden" : "text-danger text-center"}>{dashboardOrderError}</span>
                                    </div>
                                </div>
                                <div className={this.state.isApplicableForBudgetValidation ? "form-item details-list" : "hidden"}>
                                    <div className="form-group">
                                        <label>Budget Order</label>
                                        <input
                                            type="number"
                                            className="form-control "
                                            placeholder=" "
                                            value={this.state.budgetOrder}
                                            onChange={this.changeBudgetOrder}
                                        />
                                        <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.isApplicableForBudgetValidation ? (this.state.budgetOrder === ''||this.state.budgetOrder == null ? 'Enter Budget Order' : ((this.state.budgetOrder <= 0 || this.invalidNumber(this.state.budgetOrder)) ? 'Invalid Budget Order' : '')) : ''}</span>
                                    </div>
                                </div>
                                {/* </div>
                            <div className="form-data"> */}
                                <div className="form-item details-list type-style">
                                    <div className="form-group">
                                        <label>Tenure</label>
                                        <div className="select-control">
                                            <select className="form-control" value={this.state.tenureId ? this.state.tenureId : ''} onChange={this.changeTenureId}>

                                                {
                                                    this.props.tenures.map((item: any, index: number) => {
                                                        if(this.state.isApplicableForBudgetValidation)
                                                        {
                                                            if(item.type==2||item.type==0)
                                                            {
                                                                return <option key={index} value={item.id}>{item.name}</option>;
                                                            }
                                                        }
                                                        else{
                                                            return <option key={index} value={item.id}>{item.name}</option>;
                                                        }
                                                        
                                                    })
                                                }

                                            </select>
                                            <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.tenureId === '' ? 'Select Tenure' : ''}</span>
                                        </div>
                                    </div>
                                </div>
                                <div className={this.state.showTenure ? "form-item details-list" : "hidden"}>
                                    <div className="form-group">
                                        <label>Tenure Value</label>
                                        <input
                                            type="number"
                                            className="form-control "
                                            placeholder=" "
                                            value={this.state.tenureValue==null?'':this.state.tenureValue}
                                            onChange={this.changeTenureValue}
                                        />
                                        <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.showTenure ? ((this.state.tenureValue === '' || this.state.tenureValue==null)? 'Enter Tenure Value' : ((this.invalidNumber(this.state.tenureValue)||this.state.tenureValue <= 0) ? 'Invalid Tenure Value' : '')) : ''}</span>
                                    </div>
                                </div>
                                {/* </div>
                            <div className="form-data"> */}
                                {/* <div className="form-item details-list">
                                    <div className="form-group">
                                        <label>Budget Code</label>
                                        <input
                                            type="text"
                                            className="form-control "
                                            placeholder=" "
                                            value={this.state.budgetCode}
                                            onChange={this.changeBudgetCode}
                                        />
                                        <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.budgetCode === '' && this.state.isApplicableForBudgetValidation ? 'Enter Budget Code' : (this.validateName(this.state.budgetCode) ? 'Invalid Budget Code' : '')}</span>
                                    </div>
                                </div> */}

                            </div>
                        </div>

                        <div className="edit-form-container">
                            <div className="edit-form">
                                <div className="expression-table">
                                    <div className="Exp-title mb-5">
                                        <span className="dib text-primary fwb text-lg">Expression </span>

                                    </div>
                                    <div className="input-container">
                                        <input
                                            type="text"
                                            className="expression-page-form "
                                            placeholder={this.state.expression ? this.state.expression : "Expression"}
                                            disabled={true}
                                        />
                                        <span className="dib text-primary fwb text-lg edit-btn" onClick={this.openExpressionBuilder.bind(this, false)}>{this.props.targetParameterAction == 'update' ? 'Edit' : 'Create'}</span>
                                    </div>





                                    <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.expression === '' ? 'Expression is required' : ''}</span>
                                    <div className="Exp-title mb-5">
                                        <span className="dib text-primary fwb text-lg mb-5">Display Expression</span>
                                        <span  className={(enableCopy)?"dib cp text-primary underline  text-md ":"dib cursor-disabled text-muted underline   text-md "} onClick={(enableCopy)?this.copyExpression:null} title={(enableCopy)?"Click here to copy expression to display expression":''}>Same as above</span>
                                        
                                    </div>
                                    <div className="input-container">
                                        <input type="text" disabled={true} className="expression-page-form " placeholder={this.state.displayExpression ? this.state.displayExpression : "Display Expression"} />
                                        <span className="dib text-primary fwb text-lg edit-btn" onClick={this.openExpressionBuilder.bind(this, true)}>{this.props.targetParameterAction == 'update' ? 'Edit' : 'Create'}</span>
                                    </div>

                                    <span className={this.state.isValid ? "hidden" : "text-danger text-center"}>{this.state.displayExpression === '' ? 'Display Expression is required' : ''}</span>
                                </div>

                                <div className="conditions-table">
                                    <span className="dib text-primary fwb text-lg">
                                        Conditions
                                                </span>
                                    <div className="toggle-btn-wraper mt-15">
                                        <div className="toggle-btns mb-20">
                                            <label>Budget Validation</label>
                                            <section className="model-15">
                                                <div className="condition-btn">
                                                    <input type="checkbox" checked={this.state.isApplicableForBudgetValidation === true} onChange={this.changeBudgetValidation} />
                                                    <label />
                                                </div>
                                            </section>
                                        </div>
                                        {/* <div className="toggle-btns mb-20 ">
                                            <label>Budgetable</label>
                                            <section className="model-15">
                                                <div className="condition-btn">
                                                    <input type="checkbox" checked={this.state.isBudgetable} onChange={this.changeIsBudgetable} />
                                                    <label />
                                                </div>
                                            </section>
                                        </div> */}
                                        <div className={this.state.type == '1' && this.state.isApplicableForBudgetValidation ? "toggle-btns mb-20 " : "hidden"}>
                                            <label>Use for Budget Pre-populate</label>
                                            <section className="model-15">
                                                <div className="condition-btn">
                                                    <input type="checkbox" checked={this.state.useAsReferenceForBudgetPrepopulation === true} onChange={this.changeBudgetPrepopulation} />
                                                    <label />
                                                </div>
                                            </section>
                                        </div>
                                        {/* <div className="toggle-btns mb-20 ">
                                            <label>Absolute</label>
                                            <section className="model-15">
                                                <div className="condition-btn">
                                                    <input type="checkbox" disabled={this.state.isTypeGrowth || this.state.isTypePvalue} checked={this.state.isAbsolute === true || this.state.isTypePvalue} onChange={this.changeIsAbsolute} />
                                                    <label />
                                                </div>
                                            </section>
                                        </div> */}

                                        <div className="toggle-btns mb-20">
                                            <label>Show on Dashboard</label>
                                            <section className="model-15">
                                                <div className="condition-btn">
                                                    <input type="checkbox" checked={this.state.showOnDashboard === true} onChange={this.changeShowOnDashboard} />
                                                    <label />
                                                </div>
                                            </section>
                                        </div>
                                        {/* <div className="toggle-btns mb-20">
                                            <label>Budget Validation</label>
                                            <section className="model-15">
                                                <div className="condition-btn">
                                                    <input type="checkbox" checked={this.state.isApplicableForBudgetValidation} onChange={this.changeBudgetValidation} />
                                                    <label />
                                                </div>
                                            </section>
                                        </div> */}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className={this.state.tabIndex == '2' ? "suggestion-page-container clearfix active" : "suggestion-page-container clearfix"}>
                    {/* <Suggessions parameterTargetId={this.props.parameterTargetId} /> */}
                </div>
                {this.state.showBuilder && <ExpressionBuilder showBuilder={this.state.showBuilder} openExpressionBuilder={this.openExpressionBuilder}
                    selectedDataId={this.state.selectedTargetData.id} targetId={this.props.targetId} setExpression={this.setExpression}
                    setDependencies={this.setDependencies} openDimensions={this.openDimensions} isForDisplay={this.state.isForDisplay}
                    setDisplayExpression={this.setDisplayExpression} isFromSuggestions={false} expression={this.state.isForDisplay ? this.state.displayExpression : this.state.expression} isTypeGrowth={this.state.isTypeGrowth} isTypePvalue={this.state.isTypePvalue} type={this.state.type}
                    expressionMetadata={this.state.isForDisplay ? this.state.displayExpressionMetadata : this.state.expressionMetadata} dependencies={this.state.isForDisplay ? this.state.displayDependencies : this.state.dependencies} />}
            </div>
        )

    }
}