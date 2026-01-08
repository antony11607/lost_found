import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Header from './components/Header';
import HomePage from './pages/HomePage';
import ReportPage from './pages/ReportPage';
import ItemDetailPage from './pages/ItemDetailPage';
export default function App() {
    return (_jsxs(BrowserRouter, { children: [_jsx(Header, {}), _jsx("main", { children: _jsxs(Routes, { children: [_jsx(Route, { path: "/", element: _jsx(HomePage, {}) }), _jsx(Route, { path: "/report", element: _jsx(ReportPage, {}) }), _jsx(Route, { path: "/item/:id", element: _jsx(ItemDetailPage, {}) })] }) })] }));
}
