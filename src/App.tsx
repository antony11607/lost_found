import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Header from './components/Header'
import HomePage from './pages/HomePage'
import ReportPage from './pages/ReportPage'
import ItemDetailPage from './pages/ItemDetailPage'

export default function App() {
  return (
    <BrowserRouter>
      <Header />
      <main>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/report" element={<ReportPage />} />
          <Route path="/item/:id" element={<ItemDetailPage />} />
        </Routes>
      </main>
    </BrowserRouter>
  )
}
