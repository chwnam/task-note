/**
 *  @typedef PropertiesHash
 *  @type {object}
 *  @property {object} plugins
 *  @property {object} editPost
 *  @property {object} editPost
 *  @property {object} components
 *  @property {object} compose
 */
const wp = window.wp || {};
const {Button, DatePicker, Popover} = wp.components;
const {withState} = wp.compose;
const {dateI18n} = wp.date;
const {PluginPostStatusInfo} = wp.editPost;
const {registerPlugin} = wp.plugins;

const TaskNoteDatePicker = withState({
    isVisible: false,
    date: new Date()
})(({id, isVisible, date, setState}) => {
    const toggleVisible = () => {
        setState((state) => ({isVisible: !state.isVisible}));
    };
    // noinspection JSXNamespaceValidation
    return (
        <Button id={id} isLink={true} isDefault={true} onClick={toggleVisible}>
            {dateI18n('Y년 m월 d일 (D)', date, false)}
            {isVisible && (<Popover>
                    <DatePicker currentDate={date} onChange={(date) => {
                        toggleVisible();
                        // noinspection JSValidateTypes,JSUnresolvedFunction
                        wp.data.dispatch('core/editor').editPost({title: dateI18n('Y년 m월 d일 (D)', date, false) + ' 업무일지'});
                        setState({date});
                    }}/>
                </Popover>
            )}
        </Button>
    );
});

registerPlugin('task-note-date-picker', {
    render: () => (
        <PluginPostStatusInfo
            name="custom-panel"
            title="Custom Panel"
            className="custom-panel"
        >
            <label for="task-note-date-picker">일지 날짜</label>
            <div>
                <TaskNoteDatePicker id="task-note-date-picker"/>
            </div>
        </PluginPostStatusInfo>
    )
});
