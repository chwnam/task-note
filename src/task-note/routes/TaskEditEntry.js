import React, {Component} from 'react';
import TaskSingleForm from "../components/TaskSingleForm";

class TaskEditEntry extends Component {
    render() {
        return <TaskSingleForm match={this.props.match}/>
    }
}

export default TaskEditEntry;
